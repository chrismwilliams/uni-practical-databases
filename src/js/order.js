// script to calculate the values on the order pg

// get the radio buttons and Total elements
const totalAmount = document.getElementById('total')
const radiobtns = document.querySelectorAll('input[type=radio]')

// get the current total
let originalTotal = totalAmount.innerHTML;

// function called when clicking on the radio buttons
function updateValue () {
  let total = (originalTotal * this.value).toFixed(2)

  totalAmount.innerHTML = total;
}

// set an event listener for each radio button
radiobtns.forEach(radio => radio.addEventListener('change', updateValue))