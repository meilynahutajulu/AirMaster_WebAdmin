import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="flex items-center justify-center my-3 size-40">
                <AppLogoIcon/>
            </div>
            <div className="ml-1 text-center text-lg my-4">
                <span className="truncate leading-none font-bold mt-6">Welcome back! <br/></span>
            </div>
        </>
    );
}
