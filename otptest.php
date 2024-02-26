<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://www.gstatic.com/firebasejs/8.3.2/firebase.js"></script>
</head>

<body>
    <input type="text" name="mobile" id="mobile">
    <button onclick="phone_auth()">Submit</button>
    <div id="recaptcha-container"></div>
    <script>

        const firebaseConfig = {
            apiKey: "fhtfhfg",
            authDomain: "airbeau.firebaseapp.com",
            projectId: "airbeau",
            storageBucket: "airbeau.appspot.com",
            messagingSenderId: "57547567",
            appId: "1:867876:web:66b15cf31f2a345af63969",
            measurementId: "G-uyytQG94DF"
        };
        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        firebase.analytics();

    </script>
    <script>
        window.onload = function () {
            render();
        }
        function render() {
            // firebase.auth().settings.appVerificationDisabledForTesting = true;
            window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container');
            recaptchaVerifier.render();
        }
        function phone_auth() {
            const phoneNumber = document.getElementById('mobile').value;
            var testVerificationCode = "123456";
            firebase.auth().signInWithPhoneNumber(phoneNumber, window.recaptchaVerifier)
                .then(function (confirmationResult) {
                    // SMS sent. Prompt user to type the code from the message, then sign the
                    // user in with confirmationResult.confirm(code).
                    window.confirmationResult = confirmationResult;
                    console.log(confirmationResult);
                    if (confirmationResult.confirm(testVerificationCode)) {
                        alert("ok");
                    }
                    alert('sms sent');
                }).catch(function (error) {
                    console.log(error);
                    alert(error.message)
                });
        }

    </script>
</body>

</html>