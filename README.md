# kOS

KwartzlabOS (kOS for short) is a member management and access control system originally designed for Kwartzlab Makerspace in Kitchener, Ontario.

## Current Features ##

### Management Features ###

* **Membership Management** - From initial application, to hiatus requests, suspensions and withdrawals. 
* **Access Control** - Hardware lockouts that can be used for doors and tools, providing secured access for members with the ability to manage RFID keys and authorizations centrally. 
* **Team System** - Assign members to teams which can administer tools and related training & maintenance requests
* **Key Kiosk** - Browser-based app that facilitates adding new keys to the system and executing management tasks. Originally designed for a Raspberry Pi-based unit with touchscreen and RFID reader.

### User Features ###

* **kOS Dashboard** - Allows members to log in to view use space information such as member traffic, real-time tool use, upcoming events and more
* **Member Profiles** - Allows members to share interests, social media links and relevant certifications for volunteer roles (CPR, etc)

## Features In Progress ##

* **Training System** - Allows members to sign up for training courses for specific tools or general training (eg. Health & Safety). Courses will appear as a skill tree to show pre-requisites. Instructors will be able to Approve/Deny access to related tools as needed.
* **Maintenance Requests** - Provides a centralized way to handle tool maintenance and other technical requests
* **Custom Reports** - Provides a robust, customizable way to generate reports for anything from member attendance, tool use, door statistics, team organization, training & maintenance requests

## Compatible Lockout Hardware ##

* **kOS Gatekeeper Project** - Raspberry Pi-based lockout with a custom PCB & enclosures designed for NFC-based key access and tool lockout modes. Two-way communication allows for remote status updates, tool lockout (for maintenance) and door unlock events. Enclosures are 3D printed and PCB is designed with through-hole components for easy assembly.

Communication & key synchronizaion with kOS is done via a SSL-encrypted API allowing for unlimited custom hardware possibilities.
