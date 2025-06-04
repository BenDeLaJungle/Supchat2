import React, { useEffect, useState } from "react";
import { apiFetch } from "../services/api";
import "../styles/Admin.css";
import AdminHeader from "../components/ui/Adminheader";

const AdminPanel = () => {
  const [users, setUsers] = useState([]);
  const [selectedUserId, setSelectedUserId] = useState(null);
  const [editedUser, setEditedUser] = useState(null);
  const [workspaces, setWorkspaces] = useState([]);
  const [selectedWorkspaceId, setSelectedWorkspaceId] = useState(null);
  const [editedWorkspace, setEditedWorkspace] = useState(null);
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
      const data = await apiFetch("admin/workspaces");
      setWorkspaces(data);
    } catch (err) {
      setError("Erreur lors de la récupération des workspaces");
    }
  };

  const handleDeleteUser = async (id) => {
    if (!window.confirm("Supprimer cet utilisateur ?")) return;
    try {
      await apiFetch(`admin/user/${id}`, { method: "DELETE" });
      fetchUsers();
      setSelectedUserId(null);
    } catch (err) {
      setError("Échec de la suppression utilisateur");
    }
  };

  const handleSaveUser = async () => {
    if (!editedUser) return;
    try {
      await apiFetch(`admin/user/${editedUser.id}`, {
        method: "PUT",
        body: JSON.stringify({ role: editedUser.role, status: editedUser.status }),
      });
      fetchUsers();
    } catch (err) {
      setError("Échec de la mise à jour utilisateur");
    }
  };

  const handleUpdateWorkspace = async (id, updateData) => {
    try {
      await apiFetch(`admin/workspaces/${id}`, {
        method: "PUT",
        body: JSON.stringify(updateData),
      });
      fetchWorkspaces();
    } catch (err) {
      setError("Échec de la mise à jour workspace");
    }
  };

  const handleDeleteWorkspace = async (id) => {
    if (!window.confirm("Supprimer ce workspace ?")) return;
    try {
      await apiFetch(`admin/workspaces/${id}`, { method: "DELETE" });
      fetchWorkspaces();
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

  useEffect(() => {
    setEditedUser(selectedUser ? { ...selectedUser } : null);
  }, [selectedUser]);

  useEffect(() => {
    setEditedWorkspace(selectedWorkspace ? { ...selectedWorkspace } : null);
  }, [selectedWorkspace]);

  const isWorkspaceModified = editedWorkspace && (
    editedWorkspace.name !== selectedWorkspace?.name ||
    editedWorkspace.status !== selectedWorkspace?.status
  );

  return (
    <>
      <AdminHeader />
      <div className="admin-container">
        <h1>Administration</h1>
        {error && <p className="error">{error}</p>}

        <section>
          <h2>Utilisateurs</h2>
          <select onChange={(e) => {
            const value = parseInt(e.target.value);
            setSelectedUserId(isNaN(value) ? null : value);
          }}>
            <option value="">-- Sélectionner un utilisateur --</option>
            {users.sort((a, b) => a.username.localeCompare(b.username)).map((u) => (
              <option key={u.id} value={u.id}>{`${u.username} (${u.email})`}</option>
            ))}
          </select>

          {editedUser && (
            <div className="admin-form">
              <p><strong>Email:</strong> {editedUser.email}</p>
              <p><strong>Nom:</strong> {editedUser.username}</p>
              <label>Rôle:
                <select
                  value={editedUser.role}
                  onChange={(e) => setEditedUser({ ...editedUser, role: e.target.value })}>
                  <option value="ROLE_USER">Utilisateur</option>
                  <option value="ROLE_ADMIN">Admin</option>
                </select>
              </label>
              <label>Status:
                <select
                  value={editedUser.status}
                  onChange={(e) => setEditedUser({ ...editedUser, status: e.target.value })}>
                  <option value="active">Actif</option>
                  <option value="inactive">Inactif</option>
                </select>
              </label>
              <div className="button-group">
                <button onClick={handleSaveUser}>Mettre à jour</button>
                <button onClick={() => handleDeleteUser(editedUser.id)}>Supprimer</button>
              </div>
            </div>
          )}
        </section>

        <section>
          <h2>Workspaces</h2>
          <select onChange={(e) => {
            const value = parseInt(e.target.value);
            setSelectedWorkspaceId(isNaN(value) ? null : value);
          }}>
            <option value="">-- Sélectionner un workspace --</option>
            {workspaces.map((w) => (
              <option key={w.id} value={w.id}>{`${w.name} (${w.status ? "Actif" : "Inactif"})`}</option>
            ))}
          </select>

          {editedWorkspace && (
            <div className="admin-form">
              <p><strong>ID:</strong> {editedWorkspace.id}</p>
              <label>Nom:
                <input
                  value={editedWorkspace.name || ""}
                  onChange={(e) => setEditedWorkspace({ ...editedWorkspace, name: e.target.value })}
                />
              </label>
              <label>Status:
                <select
                  value={editedWorkspace.status ? "1" : "0"}
                  onChange={(e) => setEditedWorkspace({
                    ...editedWorkspace,
                    status: e.target.value === "1"
                  })}
                >
                  <option value="1">Actif</option>
                  <option value="0">Inactif</option>
                </select>
              </label>
			  <div className="button-group">
				  <button
					onClick={() =>
					  handleUpdateWorkspace(editedWorkspace.id, {
						name: editedWorkspace.name,
						status: editedWorkspace.status,
					  })
					}
					disabled={!isWorkspaceModified}
				  >
					Enregistrer
				  </button>
			  </div>
            </div>
          )}
        </section>
      </div>
    </>
  );
};

export default AdminPanel;
