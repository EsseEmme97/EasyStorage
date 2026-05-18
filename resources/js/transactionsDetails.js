const unitSelect = document.getElementById('unit');
const quantityInput = document.getElementById('quantity_value');
const quantityValueWrapper = document.getElementById('quantity-value-wrapper');
const sizesWrapper = document.getElementById('sizes-wrapper');
const sizeInputs = sizesWrapper
	? sizesWrapper.querySelectorAll('input[name^="quantity["]')
	: [];

function syncQuantityInputs() {
	if (!unitSelect || !quantityInput || !quantityValueWrapper || !sizesWrapper) {
		return;
	}

	const isSizesSelected = unitSelect.value === 'sizes';

	quantityValueWrapper.classList.toggle('hidden', isSizesSelected);
	quantityInput.required = !isSizesSelected;
	quantityInput.disabled = isSizesSelected;

	sizesWrapper.classList.toggle('hidden', !isSizesSelected);
	sizeInputs.forEach((input) => {
		input.disabled = !isSizesSelected;
	});
}

if (unitSelect) {
	unitSelect.addEventListener('change', syncQuantityInputs);
	syncQuantityInputs();
}
