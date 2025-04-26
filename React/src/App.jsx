import AppRouter from "./router/router";
import { AuthProvider } from "./context/AuthContext";
import Register from "./pages/Register";

function App() {
  return (
    <AuthProvider> {/* Permet à useAuth() de fonctionner partout */}
      <AppRouter />
    </AuthProvider>
  );
}

export default App;
