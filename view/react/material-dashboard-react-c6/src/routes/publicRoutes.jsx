import context from "variables/carbonphp";
import Dashboard from "views/Dashboard/Dashboard";
import Documentation from "views/Documentation/Documentation";
import LandingPage from "views/LandingPage/LandingPage";
import ProfilePage from "views/ProfilePage/ProfilePage";
import LoginPage from "views/LoginPage/LoginPage";


let publicRoutes = [
    {
         path: "/Dashboard",    // I'm leaving this here for the time being as an example
         name: "/Dashboard",    // This should be loaded under a different wrapper
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
        path: "/5.0",
        name: "/Documentation",
        navbarName: "Components",
        component: Documentation
    },
    {
        redirect: true,
        path: "/2.0",
        to: "http://dev.carbonphp.com:80/2.0",
        navbarName: "Version 2.0"
    },
    {
        redirect: true,
        path: "/",
        pathTo: "/5.0",
        navbarName: "Documentation"
    }
];

publicRoutes = publicRoutes.map(context.contextRoot);

export default publicRoutes;
