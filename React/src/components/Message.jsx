import React from 'react';
import '../styles/color.css';
import '../styles/chat.css';


const Message = ({ author, content, timestamp }) => {
  return (
    <div className="message">
      <div className="message-author">{author}</div>
      <div className="message-content">{content}</div>
      <div className="message-timestamp">{new Date(timestamp).toLocaleString()}</div>
    </div>
  );
};

export default Message;

 