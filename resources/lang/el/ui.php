<?php

/**
 * ui.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

return [
    'copy_link_success' => 'Ο σύνδεσμος αντιγράφηκε.',
    'copy_link_failed' => 'Δεν ήταν δυνατή η αντιγραφή του συνδέσμου.',
    'resume_attempts_help' => 'Επιτρέπονται έως 5 προσπάθειες επειδή η συνέχιση είναι ενεργή.',
    'standard_attempts_help' => 'Επιτρεπόμενο εύρος: από 1 έως 5 προσπάθειες ανά εξεταζόμενο.',
    'csv_empty_file' => 'Το αρχείο CSV είναι κενό.',
    'csv_read_error' => 'Δεν ήταν δυνατή η ανάγνωση του αρχείου CSV.',
    'csv_missing_fields' => 'Γραμμή :line: λείπουν υποχρεωτικά πεδία.',
    'csv_invalid_code' => 'Γραμμή :line: ο κωδικός πρέπει να είναι ακριβώς 4 ψηφία.',
    'csv_invalid_attempts' => 'Γραμμή :line: οι προσπάθειες πρέπει να είναι από 1 έως :max.',
    'csv_reserved_guest_code' => 'Γραμμή :line: ο κωδικός 0000 προορίζεται μόνο για guest πρόσβαση.',
    'question_csv_invalid_headers' => 'Οι επικεφαλίδες CSV πρέπει να ξεκινούν με: text, answer_1, answer_2, ..., correct_answers.',
    'question_csv_too_many_rows' => 'Το αρχείο πρέπει να περιέχει έως :max ερωτήσεις.',
    'question_csv_empty_text' => 'Γραμμή :line: το κείμενο της ερώτησης είναι υποχρεωτικό.',
];
