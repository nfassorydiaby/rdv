import React from 'react';
import medecinImage from '../assets/portrait-docteur.jpg';

function MedecinList({ nom, poste, adresse, consultationVideo, planning }) {

    const handleReservationRdv = (jour, date, heure) => {
        // Create a JSON object with jour and heure
        const reservationData = { jour, date, heure };
    
        // Convert the JSON object to a string
        const reservationDataString = JSON.stringify(reservationData);
    
        // Store the string in the local storage
        localStorage.setItem('reservationDataRdv', reservationDataString);

    
        // Perform any additional actions or navigate to the "/motif_page" if needed
        // Example: window.location.href = '/motif_page';
    };

    return (
        <section className="flex phone_flex-column mt-40 pration-trouver">
            <div className="zone-info-praticien flex-column">
                <div className="flex-center">
                    <img src={medecinImage} alt="people" />
                </div>
                <p>
                    <b className="nom-medecin">{nom}</b>
                </p>
                <p>
                    <b className="poste">{poste}</b>
                </p>
                {consultationVideo && (
                    <div className="flex flex-row flex-center">
                        <svg className="svg-icon" style={{ width: '1em', height: '1em', verticalAlign: 'middle', fill: 'black', overflow: 'hidden' }} viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg">
                            <path d="M918.613333 305.066667a42.666667 42.666667 0 0 0-42.666666 0L725.333333 379.306667A128 128 0 0 0 597.333333 256H213.333333a128 128 0 0 0-128 128v256a128 128 0 0 0 128 128h384a128 128 0 0 0 128-123.306667l151.893334 75.946667A42.666667 42.666667 0 0 0 896 725.333333a42.666667 42.666667 0 0 0 22.613333-6.4A42.666667 42.666667 0 0 0 938.666667 682.666667V341.333333a42.666667 42.666667 0 0 0-20.053334-36.266666zM640 640a42.666667 42.666667 0 0 1-42.666667 42.666667H213.333333a42.666667 42.666667 0 0 1-42.666666-42.666667V384a42.666667 42.666667 0 0 1 42.666666-42.666667h384a42.666667 42.666667 0 0 1 42.666667 42.666667z m213.333333-26.453333l-128-64v-75.093334l128-64z" />
                        </svg>
                        <p className="video-p">Consultation vidéo disponible</p>
                    </div>
                )}

                <div className="adresse">
                    <p>{adresse}</p>
                </div>
            </div>

            <div className="planning">
                <div className="flex space-between zone-calendrier">
                    {planning.map((jour, index) => (
                        <div key={index} className="day">
                            <p>{jour.jour}</p>
                            <p>{jour.date}</p>

                            {jour.heures.map((heure, idx) => (
                                heure !== "-" ? (
                                    <div className="dispo-hour" key={idx}>
                                        <p className="hour">
                                            <a href="/motif_page" onClick={() => handleReservationRdv(jour.jour, jour.date, heure)} className="hour-link">
                                                {heure}
                                            </a>
                                        </p>
                                    </div>
                                ) : (
                                    <div className="no-dispo" key={idx}>
                                        <p className="hour">{heure}</p>
                                    </div>
                                )
                            ))}

                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}

export default MedecinList;
