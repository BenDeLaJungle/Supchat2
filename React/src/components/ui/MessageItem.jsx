import React from 'react';
import '../../styles/Index.css';

export default function MessageItem({ author, text, time }) {
  return (
    <div className="message-item">
      <strong>{author}</strong> <span>({time})</span>
      <p>{text}</p>
    </div>
  );
}