// AdminSpace.jsx - version améliorée avec listes déroulantes pour utilisateurs et workspaces

import React, { useEffect, useState } from "react";
import { apiFetch } from "../services/api";
import "../styles/Admin.css";
import AdminHeader from "../components/ui/Adminheader";

const AdminPanel = () => {
  const [users, setUsers] = useState([]);
  const [selectedUserId, setSelectedUserId] = useState(null);
  const [workspaces, setWorkspaces] = useState([]);
  const [selectedWorkspaceId, setSelectedWorkspaceId] = useState(null);
  const [error, setError] = useState("");

  const fetchUsers = async () => {
    try {
      const data = await apiFetch("admin/users");
      setUsers(data);
    } catch (err) {
      setError("Erreur lors de la récupération des utilisateurs");
    }
  };

  const fetchWorkspaces = async () => {
    try {
      const data = await apiFetch("workspaces");
      setWorkspaces(data);
    } catch (err) {
      setError("Erreur lors de la récupération des workspaces");
    }
  };

  const handleDeleteUser = async (id) => {
    if (!window.confirm("Supprimer cet utilisateur ?")) return;
    try {
      await apiFetch(`admin/user/${id}`, { method: "DELETE" });
      setUsers(users.filter((u) => u.id !== id));
      setSelectedUserId(null);
    } catch (err) {
      setError("Échec de la suppression utilisateur");
    }
  };

  const handleUpdateUser = async (id, field, value) => {
    try {
      await apiFetch(`admin/user/${id}`, {
        method: "PUT",
        body: JSON.stringify({ [field]: value }),
      });
      fetchUsers();
    } catch (err) {
      setError("Échec de la mise à jour utilisateur");
    }
  };

  const handleUpdateWorkspace = async (id, field, value) => {
    try {
      await apiFetch(`workspaces/${id}`, {
        method: "PUT",
        body: JSON.stringify({ [field]: value }),
      });
      fetchWorkspaces();
    } catch (err) {
      setError("Échec de la mise à jour workspace");
    }
  };

  const handleDeleteWorkspace = async (id) => {
    if (!window.confirm("Supprimer ce workspace ?")) return;
    try {
      await apiFetch(`workspaces/${id}`, { method: "DELETE" });
      setWorkspaces(workspaces.filter((w) => w.id !== id));
      setSelectedWorkspaceId(null);
    } catch (err) {
      setError("Échec de la suppression workspace");
    }
  };

  useEffect(() => {
    fetchUsers();
    fetchWorkspaces();
  }, []);

  const selectedUser = users.find((u) => u.id === selectedUserId);
  const selectedWorkspace = workspaces.find((w) => w.id === selectedWorkspaceId);

  return (
    <>
      <AdminHeader />
      <div className="admin-container">
        <h1>Administration</h1>
        {error && <p className="error">{error}</p>}

        <section>
          <h2>Utilisateurs</h2>
          <select onChange={(e) => setSelectedUserId(parseInt(e.target.value))}>
            <option value="">-- Sélectionner un utilisateur --</option>
            {users.map((u) => (
              <option key={u.id} value={u.id}>{`${u.username} (${u.email})`}</option>
            ))}
          </select>

          {selectedUser && (
            <div className="admin-form">
              <p><strong>Email:</strong> {selectedUser.email}</p>
              <p><strong>Nom:</strong> {selectedUser.username}</p>
              <label>Rôle:
                <select
                  value={selectedUser.role}
                  onChange={(e) => handleUpdateUser(selectedUser.id, "role", e.target.value)}>
                  <option value="ROLE_USER">Utilisateur</option>
                  <option value="ROLE_ADMIN">Admin</option>
                </select>
              </label>
              <label>Status:
                <select
                  value={selectedUser.status}
                  onChange={(e) => handleUpdateUser(selectedUser.id, "status", e.target.value)}>
                  <option value="active">Actif</option>
                  <option value="inactive">Inactif</option>
                </select>
              </label>
              <button onClick={() => handleDeleteUser(selectedUser.id)}>🗑️ Supprimer</button>
            </div>
          )}
        </section>

        <section>
          <h2>Workspaces</h2>
          <select onChange={(e) => setSelectedWorkspaceId(parseInt(e.target.value))}>
            <option value="">-- Sélectionner un workspace --</option>
            {workspaces.map((w) => (
              <option key={w.id} value={w.id}>{w.name}</option>
            ))}
          </select>

          {selectedWorkspace && (
            <div className="admin-form">
              <label>Nom:
                <input
                  value={selectedWorkspace.name}
                  onChange={(e) => handleUpdateWorkspace(selectedWorkspace.id, "name", e.target.value)}
                />
              </label>
              <label>Status:
                <select
                  value={selectedWorkspace.status}
                  onChange={(e) => handleUpdateWorkspace(selectedWorkspace.id, "status", e.target.value)}>
                  <option value="active">Actif</option>
                  <option value="inactive">Inactif</option>
                </select>
              </label>
              <button onClick={() => handleDeleteWorkspace(selectedWorkspace.id)}>🗑️ Supprimer</button>
            </div>
          )}
        </section>
      </div>
    </>
  );
};

export default AdminPanel;