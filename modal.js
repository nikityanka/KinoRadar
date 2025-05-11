const modal = document.getElementById('modal');
const loginButtons = document.querySelectorAll('#loginBtn, #loginBtn2');
const toggleBtn = document.getElementById('toggleBtn');
const toggleBtnReg = document.getElementById('toggleBtnReg');
const loginForm = document.getElementById('loginForm');
const registrationForm = document.getElementById('registrationForm');
const body = document.body;
let isOpen = false;

loginButtons.forEach(button => {
    button.addEventListener('click', () => {
        modal.showModal();
        body.style.filter = 'blur(5px)';
        isOpen = true;
    });
});

toggleBtn.addEventListener('click', () => {
    loginForm.style.display = 'none';
    registrationForm.style.display = 'flex';
    document.getElementById('formTitle').innerText = 'Регистрация';
});

toggleBtnReg.addEventListener('click', () => {
    registrationForm.style.display = 'none';
    loginForm.style.display = 'flex';
    document.getElementById('formTitle').innerText = 'Вход';
});

document.querySelector('.close').addEventListener('click', () => {
    closeModal();
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.open) {
        closeModal();
    }
});

function closeModal() {
    modal.close();
    isOpen = false;
    body.style.filter = 'none';
}

/*
body.addEventListener('click', () => {
    if (!isOpen) {
        closeModal();
    }
});*/
