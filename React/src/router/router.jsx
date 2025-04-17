import { BrowserRouter, Routes, Route } from "react-router-dom";
import Home from "../pages/Home";
import Login from "../pages/Login";
import Calendrier from '../pages/calendrier';
import AdminSpace from '../pages/AdminSpace';

export default function AppRouter() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Login />} />
        <Route path="/login" element={<Login />} />
        <Route path="/home" element={<Home />} />
        <Route path="/calendrier" element={<Calendrier />} />
        <Route path="/AdminSpace" element={<AdminSpace />} />
      </Routes>
    </BrowserRouter>
  );
}
