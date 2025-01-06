document.addEventListener("DOMContentLoaded", function () {

    const tokenizeForm = document.querySelector('#tokenize-form');
    const { is_checkout } = isCheckout;

    if (jQuery('body').find(tokenizeForm).length > 0) {
        
        if(is_checkout) {
            setTimeout(() => {
                initForm(credentials, userData);
            }, 3000);
            
        } else {
            initForm(credentials, userData);
        }
    }

});

const initForm = ( credentials, userData ) => {

    const { user_email, user_id } = userData;
    const { mode, app_code, app_key } = credentials;

    //console.log(app_code);
    //console.log(app_key);

    console.log(mode);

    // === Variable to use ===
    let environment = mode;
    let application_code = app_code; // Provided by Payment Gateway
    let application_key = app_key; // Provided by Payment Gateway
    let submitButton = document.querySelector("#tokenize-form #tokenize-form-container #tokenize_btn");
    let retryButton = document.querySelector("#tokenize-form #tokenize-form-container #retry_btn");
    let submitInitialText = submitButton.textContent;

    // Get the required additional data to tokenize card
    let get_tokenize_data = () => {
        let data = {
        locale: "en",
        user: {
            id: user_id,
            email: user_email,
        },
        configuration: {
            default_country: "ECU",
        },
        };
        return data;
    };

    // 2. Instance the [PaymentGateway](#PaymentGateway-class) with the required parameters.
    let pg_sdk = new PaymentGateway(
        environment,
        application_code,
        application_key
    );

    // 3. Generate the tokenization form with the required data. [generate_tokenize](#generate_tokenize-function)
    // At this point it's when the form is rendered on page.
    pg_sdk.generate_tokenize(
        get_tokenize_data(),
        "#tokenize_example",
        responseCallback,
        notCompletedFormCallback
    );

    // 4. Define the event to execute the [tokenize](#tokenize-function) action.
    submitButton.addEventListener("click", (event) => {
        document.getElementById("response").innerHTML = "";
        submitButton.innerText = "Card Processing...";
        submitButton.setAttribute("disabled", "disabled");
        pg_sdk.tokenize();
        event.preventDefault();
    });

    // You can define a button to create a new form and save new card
    retryButton.addEventListener('click', event => {
        // re call function
        console.log('hice-click');
        submitButton.innerText = submitInitialText;
        retryButton.style.display = 'none';
        submitButton.style.display = 'block';
        submitButton.removeAttribute('disabled');
        pg_sdk.generate_tokenize(get_tokenize_data(), '#tokenize_example', responseCallback, notCompletedFormCallback);
    });

};






