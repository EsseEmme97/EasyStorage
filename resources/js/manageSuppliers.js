const toggleButton = document.getElementById("toggle-supplier-form");
const formWrapper = document.getElementById("supplier-form-wrapper");
const closingButton = document.getElementById("closingSupplierButton");
const searchInput = document.getElementById("suppliers-search");
const supplierRows = document.querySelectorAll(".supplier-row");
const noSearchResultsRow = document.getElementById("no-search-results-row");
const resetResultsButton = document.getElementById("resetResearch");

if (toggleButton && formWrapper) {
    toggleButton.addEventListener("click", () => {
        formWrapper.classList.toggle("hidden");
    });
}

if (closingButton && formWrapper) {
    closingButton.addEventListener("click", () => {
        formWrapper.classList.add("hidden");
    });
}

if (searchInput && supplierRows.length) {
    searchInput.addEventListener("input", (event) => {
        const searchTerm = event.target.value.trim().toLowerCase();
        let visibleRows = 0;

        supplierRows.forEach((row) => {
            const searchableText = (row.dataset.search || "").toLowerCase();
            const isVisible = searchableText.includes(searchTerm);

            row.classList.toggle("hidden", !isVisible);

            if (isVisible) {
                visibleRows += 1;
            }
        });

        if (noSearchResultsRow) {
            const hasNoMatches = searchTerm.length > 0 && visibleRows === 0;
            noSearchResultsRow.classList.toggle("hidden", !hasNoMatches);
        }
    });
}

resetResultsButton.addEventListener("click", () => {
    searchInput.value = "";
    supplierRows.forEach((row) => row.classList.remove("hidden"));
});
