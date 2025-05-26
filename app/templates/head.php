<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delibread ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        :root {
            --beige: #f5e6d3;
            --beige-dark: #e4d5c2;
        }

        body {
            background-color: #f8f9fa;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            height: 100vh;
            position: fixed;
            background-color: var(--beige-dark);
        }

        .main-content {
            margin-left: 280px;
            flex: 1;
        }

        .bg-beige { background-color: var(--beige); }
        .bg-beige-dark { background-color: var(--beige-dark); }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(245, 230, 211, 0.3);
        }
    </style>
</head>