# Harbor General — Hospital Management System

A multi-page hospital/clinic management web application built with HTML, CSS, JavaScript, PHP and MySQL, for **Harbor General**.

---

## 1. Project Structure

```
harbor-general/
├── index.php                Public homepage
├── about.php                 About page (mission, vision, values)
├── services.php                Services / departments page
├── doctors.php                  Public medical team page (with real photos)
├── contact.php                    Contact page + working contact form
├── login.php                        Unified login — staff and patients both sign in here
├── logout.php                         Ends whichever session (staff or patient) is active
├── patient_signup.php                   Patient self-service sign up
├── patient_portal.php                     Patient's own visit history, diagnoses & outcomes
├── dashboard.php                            Staff dashboard (stats + today's appointments)
├── patients.php                               Patient list + search
├── patient_add.php                              Register a patient (+ optional portal login)
├── patient_edit.php                              Edit a patient (+ change/remove portal login)
├── patient_view.php                                Patient profile + appointment history
├── doctors_manage.php                                Doctor list, search/filter, delete
├── doctor_add.php                                      Add a doctor (+ photo upload)
├── doctor_edit.php                                       Edit a doctor (+ photo replace/remove)
├── appointments.php                                        Appointment list, filters, status update
├── appointment_add.php                                       Book an appointment
├── appointment_diagnosis.php                                   Record diagnosis/outcome/referral
├── includes/
│   ├── db.php                                                    Database connection (PDO) + photo helper
│   ├── auth.php                                                    Session/auth helpers (staff + patients)
│   ├── header.php / footer.php                                      Public site layout
│   └── app_header.php / app_footer.php                                Staff dashboard layout (sidebar)
├── css/style.css                                                        All styling (white + hospital blue)
├── js/script.js                                                           Mobile nav, sidebar, alerts, confirm dialogs
├── uploads/doctors/                                                         Uploaded doctor photos (protected folder)
├── database.sql                                                               Full schema + seed data
└── add_patient_portal.sql                                                      Migration for an existing database
```

