const toggleButton = document.getElementById('toggle-transaction-form');
const formWrapper = document.getElementById('transaction-form-wrapper');
const closingButton = document.getElementById('closingButton');
const addSupplierLink = document.getElementById('add-supplier-link');
const descriptionInput = document.getElementById('description');
const typeSelect = document.getElementById('type');
const statusSelect = document.getElementById('status');
const supplierSelect = document.getElementById('supplier_id');

if (toggleButton && formWrapper) {
	toggleButton.addEventListener('click', () => {
		formWrapper.classList.toggle('hidden');
	});
}

if (closingButton && formWrapper) {
	closingButton.addEventListener('click', () => {
		formWrapper.classList.add('hidden');
	});
}

if (addSupplierLink) {
	addSupplierLink.addEventListener('click', () => {
		const baseUrl = addSupplierLink.dataset.baseUrl || addSupplierLink.href;
		const params = new URLSearchParams();

		params.set('from', 'transactions');

		if (descriptionInput?.value) {
			params.set('description', descriptionInput.value);
		}

		if (typeSelect?.value) {
			params.set('type', typeSelect.value);
		}

		if (statusSelect?.value) {
			params.set('status', statusSelect.value);
		}

		if (supplierSelect?.value) {
			params.set('supplier_id', supplierSelect.value);
		}

		addSupplierLink.href = `${baseUrl}?${params.toString()}`;
	});
}
