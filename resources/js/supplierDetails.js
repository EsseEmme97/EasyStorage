const editButton = document.getElementById('editName');
const nameInput = document.querySelector('input[name=name]');
let isClicked = false;

editButton.addEventListener('click', () => {
	if (!isClicked) {
		nameInput.removeAttribute('readonly');
		nameInput.focus();
		isClicked = true;
	} else {
		nameInput.setAttribute('readonly', true);
		isClicked = false;
	}
});