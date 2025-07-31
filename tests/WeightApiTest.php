<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/WeightAPI.php';

class WeightApiTest extends TestCase {
    private $tempFile;

    protected function setUp(): void {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'weights');
    }

    protected function tearDown(): void
    {
        unlink($this->tempFile);
    }

    public function testNoWeightsForUser() {
        file_put_contents($this->tempFile, json_encode([]));
        $result = WeightApi::getWeights("test@example.com", null, $this->tempFile);
        $this->assertEquals(0, $result['amount']);
        $this->assertEquals([], $result['data']);
    }

    public function testWeightsReturnedCorrectly() {
        $data = [
            "test@example.com" => [
                ["date" => "2025-01-01", "value" => 70],
                ["date" => "2025-01-02", "value" => 71],
            ]
        ];
        file_put_contents($this->tempFile, json_encode($data));
        $result = WeightAPI::getWeights("test@example.com", null, $this->tempFile);
        $this->assertEquals(2, $result['amount']);
        $this->assertEquals(70, $result['data'][0]['value']);
        $this->assertEquals(0, $result['data'][0]['id']);
    }

    public function testLimitNumberOfWeights() {
        $data = [
            "test@example.com" => [
                ["date" => "2024-01-01", "value" => 70],
                ["date" => "2024-01-02", "value" => 71],
                ["date" => "2024-01-03", "value" => 72]
            ]
        ];
        file_put_contents($this->tempFile, json_encode($data));
        $result = WeightApi::getWeights("test@example.com", 2, $this->tempFile);
        $this->assertEquals(2, $result['amount']);
        $this->assertEquals(71, $result['data'][0]['value']);
        $this->assertEquals(72, $result['data'][1]['value']);
    }

    public function testLogWeightAddsNewEntry() {
        file_put_contents($this->tempFile, json_encode([]));

        $result = WeightAPI::logWeight("test@example.com", 72.5, "2025-07-30", $this->tempFile);
        $this->assertTrue($result);

        $data = json_decode(file_get_contents($this->tempFile), true);
        $this->assertCount(1, $data["test@example.com"]);
        $this->assertEquals(72.5, $data["test@example.com"][0]["weight"]);
        $this->assertEquals("2025-07-30", $data["test@example.com"][0]["date"]);
        $this->assertEquals(0, $data["test@example.com"][0]["id"]);
    }

    public function testLogWeightAppendsCorrectly() {
        $initial = [
            "test@example.com" => [
                ["date" => "2025-07-28", "weight" => 70, "id" => 0],
            ]
        ];
        file_put_contents($this->tempFile, json_encode($initial));

        WeightAPI::logWeight("test@example.com", 71, "2025-07-31", $this->tempFile);

        $data = json_decode(file_get_contents($this->tempFile), true);
        $this->assertCount(2, $data["test@example.com"]);
        $this->assertEquals(71, $data["test@example.com"][1]["weight"]);
        $this->assertEquals(1, $data["test@example.com"][1]["id"]);
    }

    public function testLogWeightRejectsInvalidWeight() {
        $this->expectException(InvalidArgumentException::class);
        WeightAPI::logWeight("test@example.com", "invalid", "2025-07-30", $this->tempFile);
    }

    public function testLogWeightRejectsInvalidDate() {
        $this->expectException(InvalidArgumentException::class);
        WeightAPI::logWeight("test@example.com", 70, "30-07-2025", $this->tempFile);
    }
}
