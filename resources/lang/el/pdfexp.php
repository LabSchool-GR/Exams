<?php

/**
 * pdfexp.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

return [
    // result_pdf.blade.php
    'title_page' => 'Αποτελέσματα Αξιολόγησης Γνώσεων (Quiz)',
    'title' => 'Αποτελέσματα Αξιολόγησης Γνώσεων',
    'subtitle' => 'Αναλυτική παρουσίαση απαντήσεων και αξιολόγησης',
    'student_name' => 'Εξεταζόμενος/η',
    'student_code' => 'Κωδικός Εξεταζόμενου/ης',
    'attempt_id' => 'ID Προσπάθειας',
    'score_percentage' => 'Ποσοστό Επιτυχίας',
    'submission_date' => 'Ημερομηνία Υποβολής',
    'answer_summary' => 'Αναλυτικές Απαντήσεις',
    'question' => 'Ερώτηση',
    'your_answer' => 'Απάντησή σας',
    'evaluation' => 'Αξιολόγηση',
    'not_answered' => 'Δεν απαντήθηκε',
    'correct' => 'Σωστό',
    'incorrect' => 'Λάθος',
    'correct_answers_label' => 'Σωστές απαντήσεις:',
    'teacher_signature' => 'Υπογραφή Εκπαιδευτικού',
    'page_footer' => 'Σελίδα :current από :total',

    // student_info_pdf.blade.php
    'info_title' => 'Καρτέλα Δοκιμασίας και Εξεταζόμενου/ης',
    'quiz_info' => 'Πληροφορίες Δοκιμασίας Αξιολόγησης',
    'quiz_title' => 'Τίτλος Quiz',
    'quiz_description' => 'Περιγραφή',
    'quiz_code' => 'Κωδικός Quiz',
    'quiz_status' => 'Κατάσταση',
    'quiz_random_order' => 'Τυχαία Σειρά',
    'quiz_timer' => 'Χρονόμετρο',
    'quiz_resume' => 'Συνέχιση Προσπάθειας',
    'quiz_teacher' => 'Εκπαιδευτικός',
    'status_active' => 'Ενεργό',
    'status_inactive' => 'Ανενεργό',
    'yes' => 'Ναι',
    'no' => 'Όχι',
    'student_info' => 'Πληροφορίες Εξεταζόμενου/ης',
    'max_attempts' => 'Μέγιστες Προσπάθειες',
    'access_title' => 'Πρόσβαση στο Quiz',
    'start_quiz_button' => 'Έναρξη Δοκιμασίας',
    'qr_label' => 'QR για άμεση πρόσβαση',
    'or_visit' => 'Εναλλακτικά, επισκεφθείτε:',
    'insert_code' => 'και εισάγετε τον κωδικό:',
    'print_title_suffix' => '- Εκτυπώσιμη Μορφή',
    'student_name_label' => 'Ονοματεπώνυμο:',
    'registry_number_label' => 'Αριθμός Μητρώου:',
    'question_label' => 'Ερώτηση',
    'image_note' => 'Περιλαμβάνεται εικόνα που δεν εμφανίζεται στην εκτυπώσιμη μορφή.',

    'access_policy' => 'Πολιτική πρόσβασης',
    'access_policy_pin_and_links' => 'PIN εξέτασης και προσωποποιημένοι σύνδεσμοι',
    'access_policy_pin_only' => 'Μόνο PIN εξέτασης',
    'access_policy_links_only' => 'Μόνο προσωποποιημένοι σύνδεσμοι',
    'insert_pin' => 'και εισάγετε το PIN εξέτασης:',
    'personal_link_label' => 'Προσωποποιημένος σύνδεσμος',
    'pin_access_label' => 'Πρόσβαση με PIN',

    // certificate.blade.php
    'certificate_title' => 'ΒΕΒΑΙΩΣΗ ΕΠΙΤΥΧΟΥΣ ΟΛΟΚΛΗΡΩΣΗΣ',
    'certificate_body_line1' => 'Βεβαιώνεται ότι ο/η',
    'certificate_body_line2' => 'συμμετείχε επιτυχώς στην εξέταση αξιολόγησης γνώσεων',
    'certificate_program' => 'του προγράμματος:',
    'certificate_code' => 'Κωδικός Προγράμματος',
    'certificate_date' => 'Ημερομηνία Ολοκλήρωσης Εξέτασης',
    'certificate_id' => 'Αριθμός Βεβαίωσης',
    'certificate_score' => 'Ποσοστό Επιτυχίας Εξέτασης',
    'certificate_verify_label' => 'Επαλήθευση Βεβαίωσης:',
    'certificate_qr_note' => 'Σαρώστε για έλεγχο γνησιότητας',
    'certificate_disclaimer' => 'Το LabSchool Exams δεν είναι φορέας πιστοποίησης, αλλά εφαρμογή αξιολόγησης.',
    'certificate_teacher_label' => 'Ο Εκπαιδευτικός',

    // students_report_pdf.blade.php
    'students_list_title' => 'Κατάλογος Εξεταζόμενων',
    'student_list' => 'Πληροφορίες Εξεταζόμενων',
    'issue_date' => 'Ημερομηνία έκδοσης:',
];
