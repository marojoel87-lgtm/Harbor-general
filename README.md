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

## 2. Database Setup (XAMPP + phpMyAdmin)

**If setting up fresh:**
1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open `http://localhost/phpmyadmin`.
3. Click **Import**, choose `database.sql`, and click **Go**.

**If you already have an existing `harbor_general` database**, don't re-import `database.sql` (it would wipe your data). Instead, run `add_patient_portal.sql` — it adds the new columns on top of what you already have.

### Default logins (one login page for both)

| Type | Username | Password |
|---|---|---|
| Staff (Administrator) | `admin` | `admin123` |
| Patient | `amaka.johnson` | `patient123` |
| Patient | `chidi.eze` | `patient123` |

If your MySQL root user has a password, update `includes/db.php`.

⚠️ Before going live, change the default staff and sample patient passwords.

## 3. How the Unified Login Works

There is **one login page** (`login.php`) for everyone. When someone submits the form:

1. The system first checks the `staff` table for a matching username (or email) and password.
2. If that doesn't match, it checks the `patients` table for a matching username and password.
3. Whichever one matches sends the person to the right place — staff go to the Dashboard, patients go to their Portal.
4. If neither matches, one generic "Invalid username or password" message is shown (it never reveals which part was wrong, or which kind of account was being checked).

Because both account types share one pool of usernames, **every username in the system — staff or patient — must be unique**. The sign-up page and the staff-side patient forms both check this automatically before saving.

## 4. The Patient Portal — Two Ways In

- **Self-service:** a new patient can go to **Sign Up** and create their own account (name, contact details, plus a username and password) in one step — this immediately creates their patient record and logs them in.
- **Staff-assisted:** when registering or editing a patient, staff can optionally set a username and password for them directly (useful for a walk-in patient who wants portal access set up at the front desk).

Once logged in, a patient sees their own info and every appointment they've had — including, for completed visits, the **diagnosis**, the **outcome** (Treatment Successful / Referred to Another Hospital / Not Resolved), and the referral hospital's name if applicable. The portal is read-only; patients cannot edit anything.

Staff record diagnosis/outcome per appointment via the **Diagnosis** button on the Appointments page or a patient's profile page.

## 5. Core Workflow

**Public website:**
Home → About → Services → Doctors → Contact → Login / Sign Up

**Staff side:**
1. Log in with a staff account.
2. Register a patient, optionally setting a portal username/password.
3. Add a doctor (with a profile photo if you like).
4. Book an appointment.
5. Mark it Completed on the Appointments page, then click **Diagnosis** to record what was found and the outcome.
6. Log out.

**Patient side:**
1. Sign up (or log in if already registered).
2. View visit history, diagnoses, and outcomes.
3. Log out.

## 6. Notes

- Passwords (staff and patient) are hashed with PHP's `password_hash()` / verified with `password_verify()`.
- All database queries use PDO prepared statements.
- `requireLogin()` protects staff pages; `requirePatientLogin()` protects the patient portal — both redirect to the same `login.php` if not authenticated, but track completely separate sessions, so one person's staff login and another person's patient login never interfere with each other.
- A doctor cannot be deleted while they still have appointments on record.
- Doctor photos can be a direct web address (used for the sample doctors) or a file uploaded through the dashboard — both work side by side automatically.
- The `uploads/doctors/` folder has its own `.htaccess` blocking any uploaded file from ever being executed as a script.
- Color palette: white backgrounds with a hospital-blue brand color throughout (buttons, headers, sidebar, links). Status badges (appointment status, outcomes, availability) intentionally keep their own distinct colors — green/amber/red/blue — since they carry meaning and shouldn't all look the same.
