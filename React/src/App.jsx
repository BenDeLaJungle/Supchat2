import AppRouter from "./router/router";
import { AuthProvider } from "./context/AuthContext";
import { SocketProvider } from './context/SocketContext';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import NotificationListener from './components/notification/NotificationListener';



function App() {
  return (
    <AuthProvider>
      <SocketProvider>
        <AppRouter />
        <NotificationListener />
        <ToastContainer position="bottom-right" autoClose={5000} />
      </SocketProvider>
    </AuthProvider>
  );
}

export default App;
