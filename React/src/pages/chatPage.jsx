import '../styles/message.css';
import ChatWindow from '../components/chat/ChatWindow';
import AdminHeader from '../components/ui/Adminheader';

const PrivateMessage = () => (
  <>
    <AdminHeader />
    <ChatWindow channelId={1} />
  </>
);

export default PrivateMessage;
