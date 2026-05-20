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
const productNameInput = document.getElementById('product_name');

const availableProducts = productSuggestionsConfig
	? JSON.parse(productSuggestionsConfig.dataset.availableProducts || '[]')
	: [];
const availableProductSuggestions = productSuggestionsConfig
	? JSON.parse(productSuggestionsConfig.dataset.availableProductSuggestions || '[]')
	: [];
const productMetadataByName = availableProductSuggestions.reduce((map, suggestion) => {
	if (!suggestion || typeof suggestion.name !== 'string') {
		return map;
	}

	map[normalizeProductName(suggestion.name)] = suggestion;
	return map;
}, {});

function normalizeProductName(productName) {
	return String(productName || '').trim().toLowerCase();
}

function getSizeInput(size) {
	if (!sizesWrapper) {
		return null;
	}

	return sizesWrapper.querySelector(`input[name="quantity[${size}]"]`);
}

function applySizesPrefill(quantity) {
	const sizeKeys = ['xxs', 'xs', 's', 'm', 'l', 'xl'];

	sizeKeys.forEach((size) => {
		const input = getSizeInput(size);

		if (!input) {
			return;
		}

		input.value = Number.parseInt(quantity?.[size] ?? 0, 10) || 0;
	});
}

function applyProductDefaultsFromSuggestion() {
	if (!productNameInput || !unitSelect) {
		return;
	}

	const selectedProduct = productMetadataByName[normalizeProductName(productNameInput.value)];

	if (!selectedProduct || typeof selectedProduct.unit !== 'string') {
		return;
	}

	unitSelect.value = selectedProduct.unit;

	if (selectedProduct.unit === 'sizes') {
		applySizesPrefill(selectedProduct.quantity || {});
	}

	syncQuantityInputs();
}

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

	if (productNameInput) {
		productNameInput.addEventListener('input', applyProductDefaultsFromSuggestion);
		productNameInput.addEventListener('change', applyProductDefaultsFromSuggestion);
		productNameInput.addEventListener('blur', applyProductDefaultsFromSuggestion);
	}

	syncQuantityInputs();
	syncProductSuggestions();
}
