<!DOCTYPE html>
<html>
<head>
    <title>Import Excel to Database</title>
    <!-- Add other necessary head elements and CSS -->
</head>
<body>

<div class="container">
    <h2>Import Excel File</h2>

    <!-- Display success message -->
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <!-- Display error message -->
    @if ($errors->any())
    <div class="alert alert-danger">
        There were some problems with your input.<br>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ url('/import-excel') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="excel">Excel file:</label>
            <input type="file" name="excel" required>
        </div>

        <button type="submit" class="btn btn-primary">Import</button>
    </form>
</div>

</body>
</html>
