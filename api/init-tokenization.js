document.addEventListener('DOMContentLoaded', function() {

    const { is_checkout } = isCheckout;

    console.log(is_checkout);

    if (is_checkout) {

        const checkoutForm = jQuery( 'form.woocommerce-checkout' );
        checkoutForm.on('checkout_place_order', tokenizationRequest );

        setTimeout(() => {
            useSaveCardsOption();
        }, 4000);
        
    }

});

/************** Executed when was called 'tokenize' and the services response successfully. ************************/
/*******************************************************************************************************************/

let responseCallback = (response) => {

    console.log('tokenizando...');

    let submitButton = document.querySelector("#tokenize-form #tokenize-form-container #tokenize_btn");
    let retryButton = document.querySelector("#tokenize-form #tokenize-form-container #retry_btn");
    const responseElement = document.getElementById("response");
    let alertHTML = '';

    const { is_checkout } = isCheckout;

    console.log(response);

    if (response.card && response.card.status === 'valid' && response.card.bin.length > 0) {

        if(!is_checkout) {
            alertHTML = `
            <div class='alert success-alert' style="margin-bottom: 24px;">
                <p><i class="icon-info" aria-hidden="true"></i>Tarjeta agregada correctamente. Recargando página.</p>
            </div>`;
        } else {
            alertHTML = `
            <div class='alert info-alert' style="margin-bottom: 24px;">
                <p><i class="icon-info" aria-hidden="true"></i>Los datos de la tarjeta son correctos. Estamos procesando el pago.</p>
            </div>`;
        }

        setTimeout(() => {
            saveCard(response);
        }, 2000);
        

    } else if (response.card.status === 'rejected') {

        alertHTML = 
            `
            <div class='alert error-alert' style="margin-bottom: 24px;">
                <p><i class="icon-info" aria-hidden="true"></i>Hubo un problema con tu tarjeta. La conexión fue rechazada. Por favor escríbenos a <a href="mailto:hi@staging.hiitclub.online">hi@staging.hiitclub.online</a>.</p>
            </div>
        `;

    } else {
        if (response.type.includes('Card already added')) {

            alertHTML = `
                <div class='alert info-alert' style="margin-bottom: 24px;">
                    <p><i class="icon-info" aria-hidden="true"></i>Ya has agregado esta tarjeta. Si quieres actualizarla primero debes borrarla.</p>
                </div>
            `;

        }
    }

    retryButton.style.display = "block";
    submitButton.style.display = "none";
    responseElement.innerHTML = alertHTML;

    setTimeout(() => {
        document.querySelector('.alert').remove();
    }, 6000);

};

/************** Executed when was called 'tokenize' function but the form was not completed. ************************/
/*******************************************************************************************************************/

let notCompletedFormCallback = (message) => {
    let submitButton = document.querySelector("#tokenize-form #tokenize-form-container #tokenize_btn");

    let alertHTML;

    if (message.includes('Invalid Card Data')) {
        alertHTML = `
            <div class='alert error-alert' style="margin-bottom: 24px;">
                <p><i class="icon-info" aria-hidden="true"></i>Ha ocurrido un error. Por favor revisa la información de tu tarjeta.</p>
            </div>
        `;

    } else {
        alertHTML = `
            <div class='alert error-alert' style="margin-bottom: 24px;">
                <p><i class="icon-info" aria-hidden="true"></i>Ha ocurrido un error. Por favor revisa la información de tu tarjeta o contáctate con <a mailto="soporte@staging.hiitclub.online">soporte@staging.hiitclub.online</a></p>
            </div>
        `;
    }
      
    document.getElementById("response").innerHTML = alertHTML;
    submitButton.removeAttribute("disabled");
    submitButton.innerText = 'Guardar tarjeta';

    setTimeout(() => {
        document.querySelector('.alert').remove();
    }, 6000);
};

/************** Executed when the users click on "place order" in checkout page ************************************/
/*******************************************************************************************************************/

const tokenizationRequest = function (e) {
    e.preventDefault();

    const tokenizeBtn = document.querySelector('#tokenize_btn');
    const form = document.querySelector('form.checkout');
    const formInputs = document.querySelectorAll('form.checkout .woocommerce-billing-fields__field-wrapper input');

    let isEmpty = false;
    let emptyInput;

    // Verificar si hay algún campo vacío
    formInputs.forEach(function (input) {
        if (input.value.trim() === '') {
            isEmpty = true;
            emptyInput = input;

            // Resaltar el campo vacío
            input.style.border = '2px solid red';
        } else {
            // Restablecer el estilo si el campo no está vacío
            input.style.border = '1px solid #ccc';
        }
    });

    // Si hay campos vacíos, muestra una alerta y realiza un desplazamiento hacia el primer campo vacío
    if (isEmpty && emptyInput) {

        const alertMessage = "<p>Por favor, complete todos los campos antes de continuar.</p>";
        const emptyFieldsAlert = document.getElementById('emptyFieldsAlert');

        if (!emptyFieldsAlert) {

            const alertDiv = document.createElement('div');
            alertDiv.classList.add('alert', 'error-alert');
            alertDiv.id = 'emptyFieldsAlert';
            alertDiv.innerHTML = alertMessage;

            form.insertBefore(alertDiv, form.firstChild);

        } else {

            emptyFieldsAlert.innerHTML = alertMessage;

        }

        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

    } else {

        // Si no hay campos vacíos, procede con el envío del formulario
        tokenizeBtn.click();

    }

    return false;

}

/************** Excecute if the responseCallback function returns status 200 **************************************/
/*******************************************************************************************************************/

const saveCard = (tokenizedCardData) => {

    const { is_checkout } = isCheckout;
    const cardData = tokenizedCardData.card || {};

    cardData['ref'] = cardData['token'].slice(-4); 

    if(is_checkout) {

        const checkoutForm = jQuery( 'form.woocommerce-checkout' );
        const billingForm = jQuery('form.woocommerce-checkout .woocommerce-billing-fields__field-wrapper');
        
        
        for (const key in cardData) {
            if (cardData.hasOwnProperty(key)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'card_' + key;
                input.id = 'card_' + key;
                input.value = cardData[key];
                billingForm.append(input);
            }
        }
        
        checkoutForm.off('checkout_place_order', tokenizationRequest);
        checkoutForm.submit();
        
    } else {
        
        const form = document.getElementById('tokenize_form');
        form.action = window.location.href;

        for (const key in cardData) {
            if (cardData.hasOwnProperty(key)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'card_' + key;
                input.value = cardData[key];
                form.append(input);
            }
        }

        form.submit();
        
    }
    
}  

/**************************** USESAVED CARDS **************************************/
/**********************************************************************************/

const useSaveCardsOption = () => {

    const checkoutForm = jQuery( 'form.woocommerce-checkout' );

    const useSavedCardsCheckbox = document.querySelector('input#use_saved_cards');
    const nuveiPaymentMethod = useSavedCardsCheckbox.closest('div.payment_box.payment_method_nuvei');

    useSavedCardsCheckbox.addEventListener('change', function () {
        
        if (useSavedCardsCheckbox.checked) {

            nuveiPaymentMethod.classList.add('use-saved-cards');

            const billingForm = jQuery('form.woocommerce-checkout .woocommerce-billing-fields__field-wrapper');
            const cardRefInput = document.createElement('input');

            cardRefInput.classList.add('card_token');
            cardRefInput.type = 'hidden';
            billingForm.append(cardRefInput);

            checkoutForm.off('checkout_place_order', tokenizationRequest);
            checkoutForm.on('checkout_place_order', validateSelectedCard);

            const useCardButtons = document.querySelectorAll('.user-card .use-card-button');
            useCardButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    isCardSelected(e, button);
                });
            });
            
        } else {

            nuveiPaymentMethod.classList.remove('use-saved-cards');

            const cardRefInput = document.querySelector('input.card_token');
            cardRefInput.remove();

            checkoutForm.off('checkout_place_order', validateSelectedCard);
            checkoutForm.on('checkout_place_order', tokenizationRequest );
            
            const useCardButtons = document.querySelectorAll('.user-card .use-card-button');
            useCardButtons.forEach(button => {
                button.closest('.user-card').classList.remove('active');
                const newButton = button.cloneNode(true);
                newButton.textContent = 'Usar tarjeta';
                button.parentNode.replaceChild(newButton, button);
            });
            
        }

    });

}

const isCardSelected = (e, button) => {

    e.preventDefault();
    
    const userCard = button.closest('.user-card');

    if (userCard.classList.contains('active')) {
       
        userCard.classList.remove('active');
        button.textContent = 'Usar tarjeta';

        const cardRefInput = document.querySelector('input.card_token');
        cardRefInput.value = '';

    } else {

        userCard.classList.add('active');
        button.textContent = 'Dejar de usar';
       
        document.querySelectorAll('.user-card').forEach(card => {
            
            if (card !== userCard) {
                card.classList.remove('active');
                card.querySelector('.use-card-button').textContent = 'Usar tarjeta';
            }

        });

        const activeCard = document.querySelector('.user-card.active');
        const cardRef = activeCard.getAttribute('data-ref');
        const cardRefInput = document.querySelector('input.card_token');


        cardRefInput.name = 'card_token';
        cardRefInput.id = 'card_token';
        cardRefInput.value = cardRef;

    }
}
  
const validateSelectedCard = function(e)  {

    e.preventDefault();
    
    const checkoutForm = jQuery( 'form.woocommerce-checkout' );
    const cardsSection = document.querySelector('#wc-nuvei-cc-form .user-cards-container');

    const emptyFieldAlert = document.getElementById('empty-field-alert');
    const alertMessage = '<i class="icon-info" aria-hidden="true"></i><p>Debes seleccionar una tarjeta para continuar con el pago.</p>';

    const savedCards = document.querySelectorAll('.user-card');

    let cardSelected = false;

    for (const card of savedCards) {
        if (card.classList.contains('active')) {
            cardSelected = true;
            console.log(cardSelected);
        }
    }

    if (!cardSelected && !emptyFieldAlert) {
        const alertDiv = document.createElement('div');
        alertDiv.classList.add('alert', 'error-alert');
        alertDiv.id = 'empty-field-alert';
        alertDiv.innerHTML = alertMessage;
    
        cardsSection.insertBefore(alertDiv, cardsSection.firstChild);
    
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

        setTimeout(() => {
            document.getElementById('empty-field-alert').remove();
        }, 6000);

    }

    if (cardSelected) {

        console.log('entre al if');
        checkoutForm.off('checkout_place_order', validateSelectedCard);
        checkoutForm.submit();

    } 

    return false;

    
}