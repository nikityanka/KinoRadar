const burger = document.getElementById('burger');
const burger_close = document.getElementById('burger-close');
const header_nav = document.getElementById('header-nav');

const main = document.querySelector('main');
const about = document.querySelector('.about');

const footer = document.querySelector('footer');

let is_displayed = true;

function toggleSideBar() {
    if (is_displayed) {
        header_nav.style.right = '0';
        burger.style.opacity = '0';
    } else {
        header_nav.style.right = '-300%';
        burger.style.opacity = '1';
    }
    is_displayed = !is_displayed;
}

burger.addEventListener('click', toggleSideBar);
burger_close.addEventListener('click', toggleSideBar);