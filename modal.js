document.addEventListener("DOMContentLoaded", function () {
    const formBtn = document.querySelector("#form-btn");
    const modal = document.querySelector("#modal");
    const closeBtn = document.querySelector("#close-btn");

    function toggleModal(event) {
        event.preventDefault(); // Prevent form submission if it's a submit button
        modal.classList.toggle("hidden");
        modal.classList.toggle("flex");
    }

    if (formBtn) {
        formBtn.addEventListener("click", toggleModal);
    }
    if (closeBtn) {
        closeBtn.addEventListener("click", toggleModal);
    }
}); 