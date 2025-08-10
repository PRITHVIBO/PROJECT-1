const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');
const forgotPasswordLink = document.getElementById('forgot-password-link');
const backToLoginBtn = document.getElementById('back-to-login');

registerBtn.addEventListener('click', () => {
    container.classList.add("active");
    container.classList.remove("forgot");
});

loginBtn.addEventListener('click', () => {
    container.classList.remove("active");
    container.classList.remove("forgot");
});

forgotPasswordLink.addEventListener('click', (e) => {
    e.preventDefault();
    container.classList.add("forgot");
    container.classList.remove("active");
});

backToLoginBtn.addEventListener('click', () => {
    container.classList.remove("forgot");
});