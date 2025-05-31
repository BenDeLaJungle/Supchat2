import { BrowserRouter, Routes, Route } from "react-router-dom";
import Home from "../pages/Home";
import Login from "../pages/Login";
import Register from "../pages/Register";
import Calendrier from '../pages/calendrier';
import AdminSpace from '../pages/AdminSpace';
import Messaging from "../pages/Messaging";
import Parametres from '../pages/parametres';
import chatPage from "../pages/chatPage";
import UserPage from "../pages/UserPage";
import WorkspaceList from "../pages/WorkspaceList";
import WorkspaceDetail from "../pages/WorkspaceDetail";
import ChatWindow from "../components/ChatWindow";
import InviteValidation from '../components/InviteValidation';
import { useParams } from "react-router-dom";

function ChatWindowWrapper() {
  const { channelId } = useParams();
  return <ChatWindow key={channelId} channelId={parseInt(channelId)} />;
}

export default function AppRouter() {
  return (
      <Routes>
        <Route path="/" element={<Login />} />
        <Route path="/login" element={<Login />} />
		    <Route path="/register" element={<Register />} />
        <Route path="/home" element={<Home />} />
        <Route path="/messaging" element={<Messaging />} />
        <Route path="/calendrier" element={<Calendrier />} />
        <Route path="/AdminSpace" element={<AdminSpace />} />
		    <Route path="/user" element={<UserPage />} />
        <Route path="/parametres" element={<Parametres />} />
        <Route path="/test-messages" element={<chatPage />} />
        <Route path="/workspaces" element={<WorkspaceList />} />
        <Route path="/workspaces/:workspaceId" element={<WorkspaceDetail />} />
        <Route path="/channels/:channelId" element={<ChatWindowWrapper />} />
        <Route path="/private-message/:recipientId" element={<chatPage />} />
        <Route path="/invite/:token" element={<InviteValidation />} />
        <Route path="/workspaces/:workspaceId/channels/:channelId" element={<ChatWindowWrapper />} />

        
      </Routes>
  );
}
