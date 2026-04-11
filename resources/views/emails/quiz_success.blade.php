<p>Αγαπητέ/ή εκπαιδευτικέ,</p>

<p>Ο εξεταζόμενος <strong>{{ $studentName }}</strong> ολοκλήρωσε με επιτυχία τη δοκιμασία:</p>

<ul>
    <li><strong>Τίτλος δοκιμασίας:</strong> {{ $quizTitle }}</li>
    <li><strong>Ποσοστό επιτυχίας:</strong> {{ $score }}%</li>
</ul>

<p>Για περισσότερες πληροφορίες, επισκεφθείτε την εφαρμογή <strong>Αξιολόγηση Γνώσεων</strong> στον παρακάτω σύνδεσμο:</p>

<p>
    <a href="{{ route('dashboard') }}" target="_blank">
        {{ route('dashboard') }}
    </a>
</p>

<p>Με εκτίμηση,<br>
Η ομάδα υποστήριξης του Labschool.gr</p>
