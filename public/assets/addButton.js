// This file describes the logic that allow the add button to be clicked

document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.querySelector('.addButton');
    const allButtons = document.querySelectorAll('.roundButton');
    addButton.addEventListener('click', function() {
        allButtons.forEach(button => {
            button.classList.toggle('clicked');
        });
    });
});