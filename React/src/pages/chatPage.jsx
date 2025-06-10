import { useParams } from 'react-router-dom';
import '../styles/message.css';
import ChatWindow from '../components/chat/ChatWindow';
import AdminHeader from '../components/ui/Adminheader';

const ChatPage = () => {
  const { channelId} = useParams();

  return (
    <>
      <AdminHeader />
      {channelId && <ChatWindow key={channelId} channelId={parseInt(channelId)} />}
    </>
  );
};

export default ChatPage;
