import context from "variables/carbonphp";
import Dashboard from "views/UI/Dashboard"
import Documentation from "views/Documentation/Documentation";
import LandingPage from "views/LandingPage/LandingPage";
import ProfilePage from "views/ProfilePage/ProfilePage";
import LoginPage from "views/LoginPage/LoginPage";
import Components from "views/UI/Documentation";
import AccessControl from "views/AccessControl/AccessControl";


let publicRoutes = [
    // TODO - The following arn't setup
    {
        path: "/landing-page",
        name: "Landing Page Management",
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
        path: "/AccessManagement",
        name: "Access Management",
        component: AccessControl
    },
    // These are reference's to UI layouts
    {
        path: "/6.0/UI/Material-Kit",
        name: "Material Kit",
        component: Components
    },
    {
        path: "/6.0/UI/Material-Dashboard",
        name: "Material Dashboard",
        component: Dashboard
    },
    // These are possible redirects we have in place
    {
        path: "/6.0",
        name: "Documentation",
        component: Documentation
    },
    {
        redirect: true,
        path: "/2.0",
        pathTo: "https://carbonphp.com/2.0",
    },
    {
        redirect: true,
        path: "/",
        pathTo: "/6.0",
    }
    // Past here a 404 should raise on the previous controller
];

publicRoutes = publicRoutes.map(context.contextRoot);

export default publicRoutes;
