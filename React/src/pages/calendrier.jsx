import React, { useState } from 'react';
import Calendar from 'react-calendar';
import 'react-calendar/dist/Calendar.css';
import '../styles/Calendrier.css';
import AdminHeader from '../components/ui/Adminheader';

function Calendrier() {
  const [date, setDate] = useState(new Date());
  const [showForm, setShowForm] = useState(false);

  return (
    <>
      <AdminHeader />
      <div className={`calendrier-wrapper ${showForm ? 'form-open' : ''}`}>
        <div className="calendrier-gauche">
          <h1 className="calendrier-title">Votre calendrier</h1>
          <Calendar
            onChange={setDate}
            value={date}
            className="mon-calendrier"
          />
        </div>

        <div className="calendrier-droite">
          {!showForm ? (
            <button className="btn-rdv" onClick={() => setShowForm(true)}>
              + Ajouter un événement
            </button>
          ) : (
            <div className="form-event">
              <h2>Créer un événement</h2>
              <form>
                <input type="text" placeholder="Titre de l'événement" required />
                <input type="time" placeholder="Heure" required />
                <textarea placeholder="Description..." rows={4} />
                <button type="submit">Valider</button>
                <button
                  type="button"
                  className="btn-cancel"
                  onClick={() => setShowForm(false)}
                >
                  Annuler
                </button>
              </form>
            </div>
          )}
        </div>
      </div>
    </>
  );
}

export default Calendrier;
