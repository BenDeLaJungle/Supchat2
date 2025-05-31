import React from 'react';
import { useNavigate } from 'react-router-dom';
import '../../styles/index.css';
import '../../styles/index.css';

const Card = ({ title, description, image, link }) => {
    const navigate = useNavigate();
  
    return (
      <div className="card" onClick={() => navigate(link)}>
        <img
          src={image}
          alt={title}
          className={`card-img ${title === "logo-user" ? "logo-user-img" : ""}`}
        />
        {title !== "logo-user" && <h2>{title}</h2>}
        <p>{description}</p>
      </div>
    );
  };
  
  export default Card;

