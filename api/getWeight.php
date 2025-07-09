<?php
header('Content-Type: application/json');

// TODO: Actually fetch data from the files

echo json_encode([
  'dates' => ['01/07', '02/07', '03/07', '04/07', '05/07', '06/07', '07/07'],
  'weights' => [72, 71.9, 71.7, 71.8, 71.6, 71.5, 71.3]
]);
