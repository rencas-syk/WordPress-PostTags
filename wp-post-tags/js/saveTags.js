function saveTags(row) {
    var postID = Number(row.childNodes[1].textContent);
    var tags = row.childNodes[5].childNodes[0].value;

    fetch(ajax_object.ajax_url, {
        method: 'POST',
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            action: "save_post_tags",  // WordPress AJAX hook function
            post_id: postID,
            post_tags: tags,
            nonce: ajax_object.nonce  // the passed nonce, required by WP for authentication
        })
    })
    .then(response => response.json())
    .then(data => {
        displayMessage(data.success, data.data.message);
    })
    .catch(error => console.error("Error:", error));
}


function displayMessage(isSuccess, message) {
    var messageDiv = document.getElementById("post-tags-message");
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