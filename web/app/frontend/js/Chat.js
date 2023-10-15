import _ from 'underscore';
import $ from 'jquery';

export class Chat {
    
    constructor() {
        this.messages = [];

        $("#user-input").keypress((event) => {
            if (event.keyCode === 13 && !event.shiftKey) {
                this.sendMessage();
            }
        });
    };

    addMessage(role, content) {
        const message = $("<li></li>").addClass("message").addClass(role + "-message").text(`${content}`);
        $("#message-list").append(message);
        this.messages.push({"role": role, "content": content});
        $("#message-list").scrollTop($("#message-list").prop("scrollHeight"));
    };

    postMessage() {
        // Send the user's message to the server using jQuery AJAX
        const requestData = JSON.stringify(this.messages);
        $.ajax({
            type: "POST",
            url: "/chat",
            contentType: "application/json",
            data: requestData,
            success: (response) => {
                response = JSON.parse(response);
                // Add the server's response to the chat interface
                this.addMessage("assistant", response.message);
                $("#user-input").val("");
                $("#user-input").prop("disabled", false);
                // focus on userInput
                $("#user-input").focus();
            },
            error: (xhr, status, error) => {
                // Handle error cases
                console.error("Error sending message:", status);
                // check for 429 (too many requests)
                if (xhr.status === 429) {
                    // retry in a second
                    setTimeout(() => {
                        this.postMessage();
                    }, 1000);
                } else {
                }
            }
        });
    }

    sendMessage() {
        const userMessage = $("#user-input").val();
        if (userMessage.trim() === "") return;

        // Add the user's message to the chat interface
        this.addMessage("user", userMessage);
        // disable userInput
        $("#user-input").prop("disabled", true);

        this.postMessage();
    };

    render(results) {
       
    };

    run() {
    };
};