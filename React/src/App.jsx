import AppRouter from "./router/router";
import { AuthProvider } from "./context/AuthContext";
import { SocketProvider } from './context/SocketContext';


function App() {
  return (
    <AuthProvider>
      <SocketProvider>
        <AppRouter />
      </SocketProvider>
    </AuthProvider>
  );
}

export default App;
