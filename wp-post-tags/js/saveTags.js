
// update the post tags in the database via AJAX
// called when the user clicks the "Save" button in a post-tags table row
function saveTags(row) {
    var postID = Number(row.childNodes[1].textContent); // get the post ID
    var tags = row.childNodes[5].childNodes[0].value;  // get the tags from the input field

    fetch(ajax_object.ajax_url, {
        method: 'POST',
        headers: {
            "Content-Type": "application/x-www-form-urlencoded" // Encode as form data
        },
        body: new URLSearchParams({
            action: "save_post_tags",  // WordPress AJAX hook function
            post_id: postID,
            post_tags: tags,
            nonce: ajax_object.nonce  // the passed nonce, required by WP for authentication
        })
    })
    .then(response => response.json()) // parse the JSON from the response
    .then(data => {
        displayMessage(data.success, data.data.message);    // display success/error message
    })
    .catch(error => console.error("Error:", error)); // log any uncaught errors to the console
}


function displayMessage(isSuccess, message) {
    var messageDiv = document.getElementById("post-tags-message");
    // display WP green success message if successful or red error message if not
    messageDiv.innerHTML = `<div class="${isSuccess ? 'updated' : 'error'} notice is-dismissible">
                                    <p>${message}</p>
                                    <button type="button" class="notice-dismiss">
                                        <span class="screen-reader-text">Dismiss this notice.</span>
                                    </button>
                                  </div>`;
                  
    var dismissButton = messageDiv.querySelector(".notice-dismiss");
    dismissButton.addEventListener("click", function() {
        messageDiv.innerHTML = "";
    });
    
}