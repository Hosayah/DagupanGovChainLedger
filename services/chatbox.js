document.addEventListener('DOMContentLoaded', () => {
    const userInput = document.getElementById('userInput');
    const sendBtn = document.getElementById('sendBtn');
    const chatArea = document.getElementById('chatArea');
    const landingSection = document.getElementById('landingSection');

    async function sendMessage() {
        const message = userInput.value.trim();
        if (!message) return;

        // Show user message
        displayMessage(message, 'user');
        userInput.value = '';
        userInput.disabled = true;
        sendBtn.disabled = true;

        // Add loader
        const loaderDiv = document.createElement('div');
        loaderDiv.classList.add('loader');
        loaderDiv.innerHTML = `<p>Thinking...</p><hr /><hr /><hr />`;
        chatArea.appendChild(loaderDiv);
        chatArea.scrollTop = chatArea.scrollHeight;

        try {
            // Send message + extracted context text
            const response = await fetch('http://localhost:3000/chat-with-context', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message, context: pdfContext })
            });

            if (!response.ok) throw new Error(`Server error: ${response.status}`);
            const data = await response.json();
            if (data.error) throw new Error(data.error);

            chatArea.removeChild(loaderDiv);
            displayMessage(data.reply, 'bot');
        } catch (error) {
            console.error('Chat Error:', error);
            if (chatArea.contains(loaderDiv)) chatArea.removeChild(loaderDiv);
            displayMessage('Sorry, I encountered an error. Please try again later.', 'bot');
        } finally {
            userInput.disabled = false;
            sendBtn.disabled = false;
            userInput.focus();
        }
    }

    function displayMessage(text, sender) {
        if (chatArea.style.display === 'none') {
            chatArea.style.display = 'flex';
            landingSection.style.display = 'none';
        }

        let formattedText = text
            .replace(/^### (.*$)/gim, '<h3>$1</h3>')
            .replace(/^## (.*$)/gim, '<h2>$1</h2>')
            .replace(/^# (.*$)/gim, '<h1>$1</h1>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/^\s*[-*]\s+(.*)$/gm, '<li>$1</li>')
            .replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>')
            .replace(/\n/g, '<br>');

        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message', sender);
        messageDiv.innerHTML = formattedText;

        chatArea.appendChild(messageDiv);
        chatArea.scrollTop = chatArea.scrollHeight;
    }

    sendBtn.addEventListener('click', sendMessage);
    userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
});
