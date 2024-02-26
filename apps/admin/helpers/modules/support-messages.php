<div>
    <title>Support Ticket System</title>
    <style>
        #messages {
            /* max-width: 600px; */
            margin: 20px auto;
            padding: 10px;
            border: 1px solid #ccc;
            height: 200px;
            overflow-y: scroll;
        }

        .message {
            margin-bottom: 10px;
            padding: 5px;
            border: 1px solid #eee;
        }

        .message.right {
            text-align: right;
        }

        #newMessage {
            /* max-width: 600px; */
            margin: 20px auto;
            padding: 10px;
            border: 1px solid #ccc;
        }
    </style>

    <div id="messages">
        <?php foreach ($ctx->msg_list as $message) : ?>
            <div class="message <?= ($message['sender_id'] == USER['id']) ? 'right' : '' ?>">
                <p><strong><?= $message['username'] ?>:</strong> <br> <?= $message['message'] ?></p>
                <p><small><?= $message['created_at'] ?></small></p>
                <!-- You can include code to display attachments here if available -->
            </div>
        <?php endforeach; ?>
        <script>
            function scrollToBottom(containerId='messages') {
                var messagesDiv = document.getElementById(containerId);
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }
            scrollToBottom(containerId='messages');
        </script>
    </div>

    <div id="newMessage">
        <div id="resmsg"></div>
        <form id="send-msg-form" action="" method="post" enctype="multipart/form-data">
            <label for="message">New Message:</label>
            <textarea name="message" id="message" class="form-control"></textarea>
            <button id="send-msg-btn" type="button" class="my-2 btn btn-primary">Send</button>
            <input type="hidden" name="action" value="send-message">
            <input type="hidden" name="support_id" value="<?php echo $ctx->support_id; ?>">
        </form>
    </div>
    <?php pkAjax_form("#send-msg-btn", "#send-msg-form", "#resmsg"); ?>
</div>
