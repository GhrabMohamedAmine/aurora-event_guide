<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/Event.php';

// Récupérer tous les événements
$events = Event::getAll();

// Préparer les données pour le calendrier
$calendarEvents = [];
foreach ($events as $event) {
    $date = DateTime::createFromFormat('d/m/Y', $event->getDate());
    $formattedDate = $date ? $date->format('Y-m-d') : $event->getDate();
    $calendarEvents[] = [
        'id' => $event->getIdEvent(),
        'title' => $event->getTitre() . ' - ' . $event->getArtiste(),
        'start' => $formattedDate,
        'description' => $event->getDescription(),
        'location' => $event->getLieu(),
        'color' => '#602299',
        'textColor' => '#ffffff',
        'url' => 'modifier.php?id_event=' . $event->getIdEvent()
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Event Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            background-color: #602299;
            min-height: 100vh;
            color: white;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #301934;
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            padding: 20px 0;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #4a2d6b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .sidebar-header img {
            height: 40px;
            width: auto;
        }

        .sidebar-header-text {
            display: flex;
            flex-direction: column;
        }

        .sidebar-header h1 {
            font-size: 20px;
            margin-bottom: 5px;
            color: #fff;
        }

        .sidebar-header h2 {
            font-size: 14px;
            color: #bdc3c7;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0 10px;
        }

        .sidebar-menu li {
            padding: 12px 20px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 14px;
            border-radius: 4px;
            margin-bottom: 5px;
        }

        .sidebar-menu li:hover {
            background-color: #4a2d6b;
        }

        .sidebar-menu li.active {
            background-color: #602299;
        }

        .sidebar-menu li i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            width: calc(100% - 250px);
            background-color: #602299;
        }

        /* Dashboard Grid Layout */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .dashboard-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            color: #333;
        }

        .dashboard-card h3 {
            margin-bottom: 15px;
            color: #381d51;
            font-size: 16px;
        }

        .stats-card {
            background-color: #301934;
            color: white;
            padding: 20px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            min-height: 200px;
            position: relative;
            overflow: hidden;
        }

        .stats-card .number {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
            z-index: 2;
        }

        .stats-card .label {
            font-size: 14px;
            opacity: 0.8;
            z-index: 2;
        }

        .stats-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0.3;
            transition: opacity 0.3s ease;
        }

        .stats-card:hover img {
            opacity: 0.5;
        }

        /* Top Navigation */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            padding: 12px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            color: #381d51;
        }

        .search-container {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-grow: 1;
        }

        .search-bar {
            position: relative;
            flex-grow: 1;
            max-width: 400px;
        }

        .search-bar input {
            padding: 8px 12px 8px 35px;
            border: 1px solid #ddd;
            border-radius: 18px;
            font-size: 13px;
            width: 100%;
            transition: all 0.3s;
        }

        .search-bar input:focus {
            outline: none;
            border-color: #381d51;
            box-shadow: 0 0 0 2px rgba(56, 29, 81, 0.2);
        }

        .search-bar i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 14px;
        }

        .nav-links {
            display: flex;
            gap: 15px;
        }

        .nav-links a {
            text-decoration: none;
            color: #34495e;
            font-weight: 500;
            transition: all 0.3s;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 14px;
        }

        .nav-links a:hover {
            color: #381d51;
            background-color: #f0f7ff;
        }

        .main-nav {
            display: flex;
            gap: 20px;
            margin-left: 20px;
        }

        .main-nav a {
            text-decoration: none;
            color: #381d51;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .main-nav a:hover {
            background-color: #f0e6ff;
        }

        /* Content Sections */
        .content-section {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            color: #333;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .section-header h2 {
            color: #381d51;
            font-size: 20px;
        }

        .add-btn {
            background-color: #602299;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .add-btn:hover {
            background-color: #4a1a7a;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
        }

        .content-table th, .content-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .content-table th {
            background-color: #602299;
            color: white;
        }

        .content-table tr:hover {
            background-color: #f5f5f5;
        }

        .action-btn {
            padding: 5px 10px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .edit-btn {
            background-color: #2196F3;
        }

        .delete-btn {
            background-color: #f44336;
        }

        .view-btn {
            background-color: #4CAF50;
        }

        .event-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        .description-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Calendar Styles */
        .calendar-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        #calendar {
            margin-top: 20px;
        }

        .fc-event {
            cursor: pointer;
        }

        /* Footer Styles */
        .site-footer {
            background-color: white;
            padding: 20px;
            margin-left: 250px;
            text-align: center;
            box-shadow: 0 -1px 3px rgba(0,0,0,0.1);
            color: #381d51;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        .social-links a {
            color: #381d51;
            font-size: 20px;
            transition: all 0.3s;
        }

        .social-links a:hover {
            color: #602299;
            transform: translateY(-3px);
        }

        .footer-text {
            color: #666;
            font-size: 14px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content, .site-footer {
                margin-left: 200px;
                width: calc(100% - 200px);
            }
            .top-nav {
                flex-direction: column;
                gap: 15px;
            }
            .search-container {
                width: 100%;
            }
            .nav-links {
                width: 100%;
                justify-content: space-around;
            }
            .main-nav {
                margin-left: 0;
                justify-content: center;
                width: 100%;
                flex-wrap: wrap;
            }
            .content-table {
                display: block;
                overflow-x: auto;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 60px;
                overflow: hidden;
            }
            .sidebar-header-text, .sidebar-menu li span {
                display: none;
            }
            .sidebar-menu li {
                text-align: center;
                padding: 12px 5px;
            }
            .sidebar-menu li i {
                margin-right: 0;
                font-size: 18px;
            }
            .main-content, .site-footer {
                margin-left: 60px;
                width: calc(100% - 60px);
            }
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" alt="Aurora Event Logo">
            <div class="sidebar-header-text">
                <h1>Aurora Event</h1>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li class="active">
                <i class="fas fa-tachometer-alt"></i>
                <a href="index.php" style="color: inherit; text-decoration: none;">
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <i class="fas fa-user"></i>
                <a href="User.php" style="color: inherit; text-decoration: none;">
                    <span>Users</span>
                </a>
            </li>
            <li>
                <i class="fas fa-calendar-alt"></i>
                <a href="afficher.php" style="color: inherit; text-decoration: none;">
                    <span>Events</span>
                </a>
            </li>
            <li>
                <i class="fas fa-box"></i>
                <a href="Products.php" style="color: inherit; text-decoration: none;">
                    <span>Products</span>
                </a>
            </li>
            <li>
                <i class="fas fa-book"></i>
                <a href="Publications.php" style="color: inherit; text-decoration: none;">
                    <span>Publications</span>
                </a>
            </li>
            <li>
                <i class="fas fa-exclamation-circle"></i>
                <a href="sponsoring.php" style="color: inherit; text-decoration: none;">
                    <span>Sponsoring</span>
                </a>
            </li>
            <li>
                <i class="fas fa-sign-out-alt"></i>
                <a href="logout.php" style="color: inherit; text-decoration: none;">
                    <span>Déconnexion</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="search-container">
                <h2 style="font-size: 18px; color: #381d51;">Welcome to Aurora Event Dashboard</h2>
                <div class="main-nav">
                    <a href="index.php"><i class="fas fa-home"></i> Home</a>
                    <a href="afficher.php"><i class="fas fa-calendar"></i> Events</a>
                    <a href="products.php"><i class="fas fa-box"></i> Products</a>
                    <a href="Publications.php"><i class="fas fa-book"></i> Publications</a>
                    <a href="sponsoring.php"><i class="fas fa-exclamation-circle"></i> Sponsoring</a>
                </div>
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
            </div>
            <div class="nav-links">
                <a href="#"><i class="fas fa-user"></i> Profile</a>
                <a href="#"><i class="fas fa-cog"></i> Settings</a>
            </div>
        </div>

        <!-- Dashboard Stats -->
        <div class="dashboard-grid">
            <div class="stats-card">
                <img src="https://th.bing.com/th/id/OIF.78nnMmgdpe0cWMQwZuTQZg?rs=1&pid=ImgDetMain" alt="Events Background">
                <div class="number">24</div>
                <div class="label">Active Events</div>
            </div>
            
            <div class="stats-card">
                <img src="https://s3.amazonaws.com/eb-blog-wpmulti/wp-content/uploads/wpmulti/sites/3/2016/06/17101603/twenty20_e95b18fa-eec1-48f2-929e-957c1539f434-2.jpg" alt="Users Icon">
                <div class="number">1,245</div>
                <div class="label">Registered Users</div>
            </div>
            
            <div class="stats-card">
                <img src="https://th.bing.com/th/id/R.bc0a4d5e6cb1735cfc30f51437ab0395?rik=Bf1w2l5xiINafg&pid=ImgRaw&r=0" alt="Activities Icon">
                <div class="number">25</div>
                <div class="label">Reservation</div>
            </div>
        </div>

        <!-- Recent Events Section -->
        <div class="content-section">
            <div class="section-header">
                <h2>Recent Events</h2>
                <a href="ajouter.php" class="add-btn">
                    <i class="fas fa-plus"></i> Add Event
                </a>
            </div>
            
            <table class="content-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Artist</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>001</td>
                        <td><img src="https://via.placeholder.com/60" alt="Event Image" class="event-image"></td>
                        <td>BORA BORA</td>
                        <td>KBE14</td>
                        <td>30/04/2025</td>
                        <td>22:00</td>
                        <td>GHAMMARTH</td>
                        <td class="description-cell">Amazing beach party with international DJs</td>
                        <td>
                            <button class="action-btn view-btn"><i class="fas fa-eye"></i> View</button>
                            <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit</button>
                            <button class="action-btn delete-btn"><i class="fas fa-trash"></i> Delete</button>
                        </td>
                    </tr>
                    <tr>
                        <td>002</td>
                        <td><img src="https://via.placeholder.com/60" alt="Event Image" class="event-image"></td>
                        <td>Summer Festival</td>
                        <td>Various Artists</td>
                        <td>15/07/2025</td>
                        <td>18:00</td>
                        <td>City Park</td>
                        <td class="description-cell">Annual summer festival with multiple stages and food vendors</td>
                        <td>
                            <button class="action-btn view-btn"><i class="fas fa-eye"></i> View</button>
                            <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit</button>
                            <button class="action-btn delete-btn"><i class="fas fa-trash"></i> Delete</button>
                        </td>
                    </tr>
                    <tr>
                        <td>003</td>
                        <td><img src="https://via.placeholder.com/60" alt="Event Image" class="event-image"></td>
                        <td>Jazz Night</td>
                        <td>Jazz Quartet</td>
                        <td>10/05/2025</td>
                        <td>20:30</td>
                        <td>Blue Note Club</td>
                        <td class="description-cell">An evening of smooth jazz and cocktails</td>
                        <td>
                            <button class="action-btn view-btn"><i class="fas fa-eye"></i> View</button>
                            <button class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit</button>
                            <button class="action-btn delete-btn"><i class="fas fa-trash"></i> Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Calendar Section -->
        <div class="content-section">
            <div class="section-header">
                <h2>Event Calendar</h2>
            </div>
            <div class="calendar-container">
                <?php if (empty($events)): ?>
                    <p>No events to display in the calendar.</p>
                <?php else: ?>
                    <div id="calendar"></div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="social-links">
            <a href="#" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="#" target="_blank"><i class="fas fa-globe"></i></a>
        </div>
        <p class="footer-text">© 2025 Aurora Event. All rights reserved.</p>
    </footer>

    <!-- Scripts nécessaires pour FullCalendar -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/fr.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay,listMonth'
                },
                defaultView: 'month',
                defaultDate: new Date(),
                locale: 'fr',
                navLinks: true,
                editable: false,
                eventLimit: true,
                events: <?php echo json_encode($calendarEvents); ?>,
                eventRender: function(event, element) {
                    element.find('.fc-title').append('<br/><small>' + event.location + '</small>');
                    element.attr('title', event.description);
                    element.tooltip({
                        container: 'body',
                        placement: 'top',
                        trigger: 'hover'
                    });
                },
                eventClick: function(event) {
                    if (event.url) {
                        window.location.href = event.url;
                        return false;
                    }
                },
                views: {
                    listMonth: {
                        type: 'list',
                        duration: { months: 1 },
                        titleFormat: 'MMMM YYYY',
                        listDayFormat: 'dddd D',
                        noEventsMessage: 'Aucun événement ce mois-ci'
                    }
                }
            });
        });
    </script>
</body>
</html>