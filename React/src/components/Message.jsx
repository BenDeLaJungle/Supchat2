import React from 'react';

const Message = ({ author, content, timestamp }) => {
  return (
    <div className="p-2 border-b">
      <div className="font-semibold">{author}</div>
      <div>{content}</div>
      <div className="text-sm text-gray-500">{new Date(timestamp).toLocaleString()}</div>
    </div>
  );
};

export default Message;
