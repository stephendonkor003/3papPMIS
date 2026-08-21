<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers at ATTP – Join Africa’s Procurement Transformation</title>

    {{-- SEO --}}
    <meta name="description"
        content="Explore open career opportunities at ATTP and be part of Africa’s digital procurement transformation.">
    <meta name="keywords" content="ATTP careers, vacancies, procurement jobs, Africa, technology, digital procurement">
    <meta name="author" content="ATTP Team">

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- Base Styles --}}
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}">

    <style>
        :root {
            --gold: #fbbc05;
            --orange: #e16435;
            --magenta: #a70d53;
            --wine: #522b39;
            --light: #f7f4f2;
        }

        body {
            font-family: "Inter", sans-serif;
            background: var(--light);
            margin: 0;
            color: #333;
        }

        /* ================= HERO ================= */
        .career-hero {
            height: 380px;
            background: url('{{ asset('assets/images/au-headquarters.jpg') }}') center/cover no-repeat;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
        }

        .career-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(82, 43, 57, .75);
        }

        .career-hero .content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            padding: 1rem;
        }

        .career-hero h1 {
            color: var(--gold);
            font-size: 2.6rem;
        }

        /* ================= FILTER ================= */
        .filter-bar {
            background: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, .1);
            border-radius: 12px;
            max-width: 900px;
            margin: -40px auto 3rem;
            padding: 1.5rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .filter-bar input {
            padding: .8rem 1rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            width: 260px;
        }

        .filter-bar button {
            background: var(--magenta);
            color: #fff;
            border: none;
            padding: .8rem 1.6rem;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
        }

        /* ================= VACANCIES ================= */
        .vacancies {
            max-width: 1200px;
            margin: 0 auto 5rem;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .vacancy-card {
            background: #fff;
            border-radius: 15px;
            padding: 1.8rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .1);
        }

        .vacancy-card h4 {
            color: var(--wine);
        }

        .vacancy-meta {
            font-size: .9rem;
            color: var(--magenta);
            margin-bottom: .8rem;
        }

        .apply-btn {
            display: inline-block;
            margin-top: 1rem;
            background: var(--magenta);
            color: #fff;
            padding: .6rem 1.4rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }

        .apply-btn:hover {
            background: var(--orange);
        }

        /* ================= MODAL ================= */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 1rem;
        }

        .modal.active {
            display: flex;
        }

        .modal-box {
            background: #fff;
            border-radius: 15px;
            max-width: 700px;
            width: 100%;
            padding: 2rem;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.6rem;
            cursor: pointer;
            color: var(--magenta);
        }

        @media(max-width:768px) {
            .filter-bar {
                flex-direction: column
            }

            .filter-bar input,
            .filter-bar button {
                width: 100%
            }
        }
    </style>
</head>

<body>

    {{-- ================= NAVBAR ================= --}}
    <header class="navbar">
        <div class="logo">
            <img src="{{ asset('assets/images/3pap-logo-white.png') }}" alt="ATTP" style="height:40px">
        </div>

        <nav class="nav-links">
            <a href="/">Home</a>
            <a href="#vacancies">Vacancies</a>
            <a href="{{ route('careers.index') }}" class="active">Careers</a>
            <a href="{{ route('login') }}">Login</a>
        </nav>
    </header>

    {{-- ================= HERO ================= --}}
    <section class="career-hero">
        <div class="content">
            <h1>Build Your Career With ATTP</h1>
            <p>
                Join a purpose-driven team transforming public procurement across Africa through
                technology, transparency, and innovation.
            </p>
        </div>
    </section>

    {{-- ================= FILTER ================= --}}
    <div class="filter-bar" id="vacancies">
        <input type="text" id="searchInput" placeholder="Search vacancies...">
        <button onclick="filterVacancies()">Search</button>
    </div>

    {{-- ================= VACANCIES ================= --}}
    <section class="vacancies" id="vacanciesContainer">

        @forelse($vacancies as $vacancy)
            <div class="vacancy-card">
                <h4>{{ $vacancy->title }}</h4>
                <div class="vacancy-meta">
                    Location: {{ $vacancy->location ?? 'Remote / Africa' }}
                </div>

                <p>{{ \Illuminate\Support\Str::limit(strip_tags($vacancy->description), 150) }}</p>

                <button class="apply-btn" data-id="{{ $vacancy->id }}" data-title="{{ $vacancy->title }}"
                    data-description="{{ strip_tags($vacancy->description) }}"
                    data-location="{{ $vacancy->location ?? 'Remote / Africa' }}" onclick="openApplyModal(this)">
                    Apply Now
                </button>
            </div>
        @empty
            <p style="grid-column:1/-1;text-align:center;">
                No vacancies available at the moment.
            </p>
        @endforelse

    </section>

    {{-- ================= APPLY MODAL ================= --}}
    <div class="modal" id="applyModal">
        <div class="modal-box">
            <button class="close-btn" onclick="closeApplyModal()">×</button>

            <h3 id="modalTitle"></h3>
            <p id="modalLocation" style="color:var(--magenta);font-weight:600;"></p>
            <p id="modalDescription"></p>

            <form method="POST" action="{{ route('vacancies.apply.store') }}" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="vacancy_id" id="vacancy_id">

                <input type="text" name="full_name" placeholder="Full Name" required class="form-control mb-3">
                <input type="email" name="email" placeholder="Email Address" required class="form-control mb-3">
                <input type="text" name="phone" placeholder="Phone Number" required class="form-control mb-3">

                <label>Upload CV</label>
                <input type="file" name="resume" required>

                <label>Upload Cover Letter</label>
                <input type="file" name="cover_letter" required>

                <button type="submit" class="apply-btn" style="width:100%;">
                    Submit Application
                </button>
            </form>
        </div>
    </div>

    {{-- ================= JS ================= --}}
    <script>
        function openApplyModal(btn) {
            document.getElementById('applyModal').classList.add('active');
            document.getElementById('modalTitle').innerText = btn.dataset.title;
            document.getElementById('modalLocation').innerText = btn.dataset.location;
            document.getElementById('modalDescription').innerText = btn.dataset.description;
            document.getElementById('vacancy_id').value = btn.dataset.id;
        }

        function closeApplyModal() {
            document.getElementById('applyModal').classList.remove('active');
        }

        function filterVacancies() {
            const q = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('.vacancy-card').forEach(card => {
                card.style.display = card.innerText.toLowerCase().includes(q) ? 'block' : 'none';
            });
        }
    </script>

</body>

</html>
