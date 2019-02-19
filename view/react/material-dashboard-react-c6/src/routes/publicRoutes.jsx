import context from "variables/carbonphp";
import Dashboard from "views/Dashboard/Dashboard";
import Components from "views/Components/Components";
import LandingPage from "views/LandingPage/LandingPage";
import ProfilePage from "views/ProfilePage/ProfilePage";
import LoginPage from "views/LoginPage/LoginPage";


let publicRoutes = [
    {
        path: "/Dashboard",
        name: "/Dashboard",
        navbarName: "/Dashboard",
        component: Dashboard
    },
    {
        path: "/landing-page",
        name: "/LandingPage",
        navbarName: "/LandingPage",
        component: LandingPage
    },
    {
        path: "/profile-page",
        name: "/ProfilePage",
        navbarName: "/ProfilePage",
        component: ProfilePage
    },
    {
        path: "/login-page",
        name: "/LoginPage",
        navbarName: "/LoginPage",
        component: LoginPage
    },
    {
        path: "/",
        name: "/Components",
        navbarName: "/Components",
        component: Components
    },
    {
        redirect: true,
        path: "/",
        to: "/5.0",
        navbarName: "Components"
    }
];

publicRoutes = publicRoutes.map(context.contextRoot);

export default publicRoutes;
