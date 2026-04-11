<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Ερώτηση</th>
            <th>Σωστές</th>
            <th>Λανθασμένες</th>
            <th>Ποσοστό</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($stats as $index => $row)
            @php
                $total = $row['correct'] + $row['wrong'];
                $percentage = $total > 0 ? round(($row['correct'] / $total) * 100, 1) : 0;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row['question'] }}</td>
                <td>{{ $row['correct'] }}</td>
                <td>{{ $row['wrong'] }}</td>
                <td>{{ $percentage }}%</td>
            </tr>
        @endforeach
    </tbody>
</table>
