import { BrowserRouter, Routes, Route } from "react-router-dom";
import Home from "../pages/Home";
import Login from "../pages/Login";
import Register from "../pages/Register";
import Calendrier from '../pages/calendrier';
import AdminSpace from '../pages/AdminSpace';
import Messaging from "../pages/Messaging";
import Parametres from '../pages/Parametres';
import PrivateMessage from "../pages/privateMessage";

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
        <Route path="/parametres" element={<Parametres />} />
        <Route path="/test-messages" element={<PrivateMessage />} />
      </Routes>
  );
}
