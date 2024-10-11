import './bootstrap';

const contentEl = document.getElementById('content');
const userId = 3;
Echo.private(`chats.${userId}`)
    .listen('.chats.created', (e) => {
        console.log('chats.created', e);
        const chat = e.chat;
        const chatEl = document.getElementById(`chat-${chat.id}`);
        let name = chat.name;
        if (chat.type === 'private') {
            const partner = chat.participants.filter(user => +user.id !== +userId);
            name = partner[0].name;
        }
        if (chatEl) {
            chatEl.innerHTML = `<div id="chat-${chat.id}">${name} (${chat.last_message?.content || 'New chat'})</div>`;
        } else {
            contentEl.innerHTML += `<div id="chat-${chat.id}">${name} (${chat.last_message?.content || 'New chat'})</div>`;
        }
    })
    .listen('.chats.updated', (e) => {
        console.log('chats.updated', e);
        const chat = e.chat;
        const chatEl = document.getElementById(`chat-${chat.id}`);
        if (chatEl) {
            let name = chat.name;
            if (chat.type === 'private') {
                const partner = chat.participants.filter(user => +user.id !== +userId);
                name = partner[0].name;
            }
            chatEl.innerHTML = `<div id="chat-${chat.id}">${name} (${chat.last_message?.content || 'New chat'})</div>`;
        }
    })
    .listen('.chats.messages.sent', (e) => {
        console.log('chats.messages.sent', e);
        const message = e.message;
        const chatEl = contentEl.querySelector(`#chat-${message.chat_id}`);
        console.log(`#chat-${message.chat_id}`, chatEl)
        if (chatEl) {
            chatEl.innerHTML += `<p class="message-${message.id}" style="margin-left: 10px">${message.content}</p>`;
        } else {
            const chat = message.chat;
            let name = chat.name;
            if (chat.type === 'private') {
                const partner = chat.participants.filter(user => +user.id !== +userId);
                name = partner[0].name;
            }
            if (chatEl) {
                chatEl.innerHTML = `<div id="chat-${chat.id}">${name} (${chat.last_message?.content || 'New chat'})</div>`;
            } else {
                contentEl.innerHTML += `<div id="chat-${chat.id}">${name} (${chat.last_message?.content || 'New chat'})</div>`;
            }
        }
    });
