/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
import './bootstrap';

function getChatroomMessages(url) {
    fetch(url)
        .then(function(data) {
            if (data.ok) {
                return data.text();
            }
            throw new Error();
        })
        .then((responseText) => {
            document.getElementById('lobby_messages_wrapper').innerHTML = responseText;
        })
        .catch((err) => {
            console.log('There was an error ', err);
        }) ;
}

if (GET_MESSAGES_URL.length > 0) {
    getChatroomMessages(GET_MESSAGES_URL);
    setInterval(() => getChatroomMessages(GET_MESSAGES_URL), 10000);
}
