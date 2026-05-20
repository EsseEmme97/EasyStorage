const unitSelect = document.getElementById('unit');
const quantityInput = document.getElementById('quantity_value');
const quantityValueWrapper = document.getElementById('quantity-value-wrapper');
const sizesWrapper = document.getElementById('sizes-wrapper');
const sizeInputs = sizesWrapper
	? sizesWrapper.querySelectorAll('input[name^="quantity["]')
	: [];
const productSuggestionsConfig = document.getElementById('product-suggestions-config');
const productNameSuggestions = document.getElementById('product_name_suggestions');
const productSuggestionFallback = document.getElementById('product-suggestion-fallback');

const availableProducts = productSuggestionsConfig
	? JSON.parse(productSuggestionsConfig.dataset.availableProducts || '[]')
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

function syncProductSuggestions() {
	if (!productNameSuggestions) {
		return;
	}

	productNameSuggestions.innerHTML = '';
	availableProducts.forEach((suggestion) => {
		const option = document.createElement('option');
		option.value = suggestion;
		productNameSuggestions.append(option);
	});

	if (!productSuggestionFallback) {
		return;
	}

	if (availableProducts.length === 0) {
		productSuggestionFallback.textContent = 'Nessun prodotto disponibile. Inserimento bloccato se la quantita supera la disponibilita confermata.';
		productSuggestionFallback.classList.remove('hidden');
		return;
	}

	productSuggestionFallback.classList.add('hidden');
	productSuggestionFallback.textContent = '';
}

if (unitSelect) {
	unitSelect.addEventListener('change', () => {
		syncQuantityInputs();
		syncProductSuggestions();
	});
	syncQuantityInputs();
	syncProductSuggestions();
}
