import { BrowserRouter, Routes, Route } from "react-router-dom";
import Home from "../pages/Home";
import Login from "../pages/Login";
import Register from "../pages/Register";
import Calendrier from '../pages/calendrier';
import AdminSpace from '../pages/AdminSpace';
import Messaging from "../pages/Messaging";
import Parametres from '../pages/parametres';
import ChatPage from "../pages/chatPage";
import UserPage from "../pages/UserPage";
import WorkspaceList from "../pages/WorkspaceList";
import WorkspaceDetail from "../pages/WorkspaceDetail";
import InviteValidation from '../pages/InviteValidation';
import Notifications from '../pages/Notifications';
import SharedFiles from '../pages/SharedFiles';

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
		    <Route path="/users/:username" element={<UserPage />} />
        <Route path="/parametres" element={<Parametres />} />
        <Route path="/test-messages" element={<ChatPage />} />
        <Route path="/workspaces" element={<WorkspaceList />} />
        <Route path="/workspaces/:workspaceId" element={<WorkspaceDetail />} />
        <Route path="/channels/:channelId" element={<ChatPage />} />
        <Route path="/private-message/:channelId" element={<ChatPage />} />
        <Route path="/invite/:token" element={<InviteValidation />} />
        <Route path="/workspaces/:workspaceId/channels/:channelId" element={<ChatPage />} />
        <Route path="/notifications" element={<Notifications />} />
		<Route path="/shared-files" element={<SharedFiles />} />
        
      </Routes>
  );
}
