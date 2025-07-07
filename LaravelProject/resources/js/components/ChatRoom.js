import React, { useEffect, useState, useRef } from 'react';
import Echo from 'laravel-echo';
import axios from 'axios';

const ChatRoom = ({ roomId, user }) => {
    const [messages, setMessages] = useState([]);
    const [members, setMembers] = useState([]);
    const [input, setInput] = useState('');
    const echoRef = useRef(null);

    useEffect(() => {
        // 取得歷史訊息
        axios.get(`/api/chat/${roomId}/messages`).then(res => setMessages(res.data));

        // 初始化 Echo
        if (!window.Echo) {
            window.Pusher = require('pusher-js');
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: 'xxx', // 請替換
                cluster: 'mt1',
                wsHost: window.location.hostname,
                wsPort: 6001,
                forceTLS: false,
                disableStats: true,
            });
        }
        echoRef.current = window.Echo;

        // Presence Channel 成員同步
        const channel = window.Echo.join(`chat.${roomId}`)
            .here(users => setMembers(users))
            .joining(user => setMembers(members => [...members, user]))
            .leaving(user => setMembers(members => members.filter(u => u.id !== user.id)))
            .listen('.MessageCreated', e => {
                setMessages(msgs => [...msgs, e.model]);
            });

        return () => {
            window.Echo.leave(`chat.${roomId}`);
        };
    }, [roomId]);

    const sendMessage = async (e) => {
        e.preventDefault();
        if (!input.trim()) return;
        await axios.post(`/api/chat/${roomId}/messages`, { content: input });
        setInput('');
    };

    return (
        <div>
            <div>成員：{members.map(u => u.name).join(', ')}</div>
            <div style={{height: 300, overflowY: 'auto', border: '1px solid #ccc', margin: '10px 0'}}>
                {messages.map(msg => (
                    <div key={msg.id}><b>{msg.user?.name || msg.user_id}：</b>{msg.content}</div>
                ))}
            </div>
            <form onSubmit={sendMessage}>
                <input value={input} onChange={e => setInput(e.target.value)} placeholder="輸入訊息..." />
                <button type="submit">送出</button>
            </form>
        </div>
    );
};

export default ChatRoom; 