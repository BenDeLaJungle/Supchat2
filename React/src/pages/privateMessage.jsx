import '../styles/message.css';
import ChatWindow from '../components/ChatWindow';
import AdminHeader from './Adminheader';

const PrivateMessage = () => (
  <>
    <AdminHeader />
    <ChatWindow channelId={1} />
  </>
);

export default PrivateMessage;
