document.addEventListener('DOMContentLoaded', function() {
    const deleteCardForms = document.querySelectorAll('.delete-card-form');
    const confirmationModal = document.querySelector('.confirmation-modal');
    const yesButton = document.querySelectorAll('.confirmation-modal button.yes');
    const noButton = document.querySelectorAll('.confirmation-modal button.no');

    let currentForm = null;

    deleteCardForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            confirmationModal.style.display = 'flex';
            currentForm = form;
        });
    });

    yesButton.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentForm) {
                currentForm.submit();
            } else {
                return;
            }
        });
    }); 

    noButton.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            confirmationModal.style.display = 'none';
        });
    }); 
});