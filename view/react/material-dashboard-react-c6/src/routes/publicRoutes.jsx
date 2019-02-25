import context from "variables/carbonphp";
import Dashboard from "views/UI/Dashboard"
import Documentation from "views/Documentation/Documentation";
import LandingPage from "views/LandingPage/LandingPage";
import ProfilePage from "views/ProfilePage/ProfilePage";
import LoginPage from "views/LoginPage/LoginPage";
import Components from "views/UI/Documentation";


let publicRoutes = [
    {
        path: "/landing-page",
        name: "Landing Page",
        component: LandingPage
    },
    {
        path: "/profile-page",
        name: "Profile Page",
        component: ProfilePage
    },
    {
        path: "/login-page",
        name: "Login Page",
        component: LoginPage
    },
    {
        path: "/5.0/UI/Material-Kit",
        name: "Material Kit",
        component: Components
    },
    {
        path: "/5.0/UI/Material-Dashboard",
        name: "Material Dashboard",
        component: Dashboard
    },
    {
        path: "/5.0",
        name: "Documentation",
        component: Documentation
    },
    {
        redirect: true,
        path: "/2.0",
        to: "http://dev.carbonphp.com:80/2.0",
    },
    {
        redirect: true,
        path: "/",
        pathTo: "/5.0",
    }
];

publicRoutes = publicRoutes.map(context.contextRoot);

export default publicRoutes;
