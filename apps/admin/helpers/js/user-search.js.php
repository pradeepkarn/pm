<script>
    document.getElementById("userSearchInput").addEventListener("input", function() {
        let input = this.value;
        let suggestionList = document.getElementById("suggestionList");
        suggestionList.innerHTML = ""; // Clear previous suggestions

        if (input.length > 0) {
            // Make an AJAX request to fetch suggestions from the server
            let xhr = new XMLHttpRequest();
            xhr.open("GET", "<?php echo BASEURI . route('searchUsersApi'); ?>?q=" + input, true);
            xhr.onload = function() {
                // console.log(this.responseText);
                let data = JSON.parse(this.responseText);
                if (data.success === true) {
                    data.data.forEach(function(user) {
                        let listItem = document.createElement("li");
                        listItem.classList.add('pk-pointer');
                        listItem.classList.add('my-3');
                        listItem.textContent = user.username;
                        listItem.addEventListener("click", function() {
                            document.getElementById("user_id").value = user.id;
                            document.getElementById("username").value = user.username;
                            document.getElementById("email").value = user.email;
                            document.getElementById("first_name").value = user.first_name;
                            document.getElementById("last_name").value = user.last_name;
                            document.getElementById("isd_code").value = user.isd_code;
                            document.getElementById("mobile").value = user.mobile;
                            suggestionList.innerHTML = ""; // Clear suggestions when user is selected
                        });
                        suggestionList.appendChild(listItem);
                    });
                } else {
                    let listItem = document.createElement("li");
                    listItem.textContent = "No user found";
                    suggestionList.appendChild(listItem);
                }

            };
            xhr.send();
        }
    });
</script>