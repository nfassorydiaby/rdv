import React, { useState, useEffect } from "react";
import SearchForm from "../SearchForm";
import "../../assets/css/search_page.css";
import { useNavigate } from "react-router-dom";
import Map from "../Map";
import DisponibilityForm from "../DisponibilityForm";
import MedecinList from "../MedecinList";
import medecinsData from "../../assets/sample.json"; // Mettez le bon chemin

function Search_page() {
  // const [medecins, setMedecins] = useState([]);
  const navigate = useNavigate();

  const [provisionEmployees, setProvisionEmployees] = useState([]);

  const token = localStorage.getItem("jwtToken");

  useEffect(() => {
    // Ici, vous pouvez charger les données JSON et les stocker dans l'état local
    // Par exemple, si vous chargez les données une fois au chargement de la page :

    // const fetchEmployeePlanning = async () => {
    //   try {
    //     const response = await fetch(`http://localhost:8888/api/userEmployees`, {
    //       method: 'GET',
    //       headers: {
    //         'Content-Type': 'application/json',
    //         'Authorization': `Bearer ${token}`,
    //         // You may include other headers like authorization if needed
    //       },
    //       // You can include other options like credentials, mode, etc.
    //     });

    //     if (!response.ok) {
    //       throw new Error('Failed to fetch employees');
    //     }

    //     const data = await response.json();
    //     console.log(data['hydra:member'])
    //     setMedecins(data['hydra:member']);
    //   } catch (error) {
    //     console.error('Error fetching employees:', error);
    //   }
    // };

    // fetchEmployeePlanning();

    const fetchProvisionEmployee = async () => {
      try {
        const response = await fetch(
          `http://localhost:8888/api/provision_employees`,
          {
            method: "GET",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${token}`,
              // You may include other headers like authorization if needed
            },
            // You can include other options like credentials, mode, etc.
          }
        );

        if (!response.ok) {
          throw new Error("Failed to fetch employees");
        }

        const data = await response.json();
        console.log(data["hydra:member"]);
        setProvisionEmployees(data["hydra:member"]);
      } catch (error) {
        console.error("Error fetching employees:", error);
      }
    };

    fetchProvisionEmployee();
  }, []);

  const handleNavigate = (id) => {
    return () => {
      navigate(
        `/medecin`,
        {
          state: {
            provisionEmployees: provisionEmployees,
          },
        },
        { replace: true }
      );
    };
  };

  return (
    <>
      <SearchForm />
      <div className="ma-80">
        <div className="flex mt-40 space-between gap-10 w-full">
          <div className="map w-5/12">
            <Map />
          </div>
          <div className="w-6/12">
            {/* {medecins && (
            <div>
              {medecins.map((medecin, index) => (
                <MedecinList
                  key={index}
                  nom={""}
                  poste={""}
                  adresse={""}
                  consultationVideo={true}
                  planning={medecin}
                />
              ))} 
            </div>
            )} */}

            {provisionEmployees && (
              <div className="grid md:grid-cols-2 gap-8">
                {provisionEmployees.map((provisionEmployee, index) => (
                  <li
                    className="bg-white shadow rounded-lg p-6 mb-8 mw-1/3"
                    key={index}
                    onClick={handleNavigate(provisionEmployee)}
                  >
                    <div className="flex flex-row gap-8">
                      <div className="flex align-middle">
                        <img
                          src="https://randomuser.me/api/portraits/men/94.jpg"
                          className="w-32 h-32 bg-gray-300 rounded-full mb-4 shrink-0"
                        ></img>
                      </div>

                      <div className="flex flex-col">
                        <p className="">
                          {" "}
                          Mr/Mme {provisionEmployee.employee.firstname}{" "}
                          {provisionEmployee.employee.lastname}
                        </p>
                        <p className="font-bold">
                          {provisionEmployee.provision.name}
                        </p>
                        <p className="text-gray-600">
                          {provisionEmployee.provision.Establishment.name}
                        </p>
                        <p className="text-gray-600">
                          {provisionEmployee.provision.Establishment.adress}
                        </p>
                      </div>
                    </div>
                  </li>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    </>
  );
}

export default Search_page;
