import context from "variables/carbonphp";
// @material-ui/icons
import Dashboard from "@material-ui/icons/Dashboard";
import Person from "@material-ui/icons/Person";
// import ContentPaste from "@material-ui/icons/ContentPaste";
import LibraryBooks from "@material-ui/icons/LibraryBooks";
import BubbleChart from "@material-ui/icons/BubbleChart";
import LocationOn from "@material-ui/icons/LocationOn";
import Notifications from "@material-ui/icons/Notifications";
import Unarchive from "@material-ui/icons/Unarchive";
// core components/views
import DashboardPage from "views/Dashboard/Dashboard.jsx";
import UserProfile from "views/UserProfile/UserProfile.jsx";
import TableList from "views/TableList/TableList.jsx";
import Typography from "views/Typography/Typography.jsx";
import Icons from "views/Icons/Icons.jsx";
import Maps from "views/Maps/Maps.jsx";
import NotificationsPage from "views/Notifications/Notifications.jsx";
import UpgradeToPro from "views/UpgradeToPro/UpgradeToPro.jsx";


let privateRoutes = [
    {
        path: "/dashboard",
        sidebarName: "Dashboard",
        navbarName: "Material Dashboard",
        name: "Material Dashboard",
        icon: Dashboard,
        component: DashboardPage
    },
    {
        path: "/wp-admin/admin.php?page=CarbonPHP",
        sidebarName: "Dashboard",
        navbarName: "Material Dashboard",
        name: "Material Dashboard",
        icon: Dashboard,
        component: DashboardPage
    },
    {
        path: "/user",
        sidebarName: "User Profile",
        navbarName: "Profile",
        name: "Profile",
        icon: Person,
        component: UserProfile
    },
    {
        path: "/table",
        sidebarName: "Table List",
        navbarName: "Table List",
        name: "Table List",
        icon: "content_paste",
        component: TableList
    },
    {
        path: "/typography",
        sidebarName: "Typography",
        navbarName: "Typography",
        name: "Typography",
        icon: LibraryBooks,
        component: Typography
    },
    {
        path: "/icons",
        sidebarName: "Icons",
        navbarName: "Icons",
        name: "Icons",
        icon: BubbleChart,
        component: Icons
    },
    {
        path: "/maps",
        sidebarName: "Maps",
        navbarName: "Map",
        name: "Map",
        icon: LocationOn,
        component: Maps
    },
    {
        path: "/notifications",
        sidebarName: "Notifications",
        navbarName: "Notifications",
        name: "Notifications",
        icon: Notifications,
        component: NotificationsPage
    },
    {
        path: "/upgrade-to-pro",
        sidebarName: "Upgrade To PRO",
        navbarName: "Upgrade To PRO",
        name: "Upgrade To PRO",
        icon: Unarchive,
        component: UpgradeToPro
    },
    {
        redirect: true,
        path: "/",
        pathTo: "/dashboard",
        navbarName: "Redirect",
        name: "Redirect"
    }
];


privateRoutes = privateRoutes.map(context.contextRoot);

export default privateRoutes;
