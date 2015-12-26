/* tiny program to run a program suid root */
#define SCRIPT "/usr/local/buildserver/setup_web"
main(argc, argv)
char **argv;
{
    setuid(0);
    seteuid(0);
    execv(SCRIPT, argv);
}
