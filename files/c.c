//client
#include <winsock2.h>
#include<winsock.h>
#include<mysql.h>
#include<stdio.h> //printf
#include<string.h>    //strlen
#include<unistd.h>    //usleep
#include<fcntl.h> //fcntl
#include<stdlib.h>
#pragma comment(lib,"ws2_32.lib") //Winsock Library
int receive_basic(int m_socket);
#define CHUNK_SIZE  512
void finish_with_error(MYSQL *con)
{  fprintf(stderr, "%s\n", mysql_error(con));
	mysql_close(con);
	exit(1);        
}int main()
{	char sendbuf4[] ="your about to check your loan repayment_details the command is right";
	char sendbuf3[] ="your about to check your loan status because the command is right";
	char sendbuf2[] ="your about to check your benefits because the command is right";
	char sendbuf1[] ="your about to check your contribution because the command is right";
	char stoname1[]="contribution check",stoname2[]="benefits check",stoname3[]="loan status",stoname4[]="loan repayment_details";
	WSADATA wsaData;
	int iResult = WSAStartup(MAKEWORD(2,2), &wsaData);
	if (iResult != NO_ERROR)
	printf("Client: Error at WSAStartup().\n");

	SOCKET m_socket;
	m_socket = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);
	if (m_socket == INVALID_SOCKET){
		printf("Client: socket() - Error at socket(): %ld\n", WSAGetLastError());
		WSACleanup();
		return 0;
	}

	struct sockaddr_in clientService;
	clientService.sin_family = AF_INET;
	clientService.sin_addr.s_addr = inet_addr("127.0.0.1");
	clientService.sin_port = htons(55555);	
	if (connect(m_socket, (SOCKADDR*)&clientService, sizeof(clientService)) == SOCKET_ERROR)
	{
		printf("Client: connect() - Failed to connect.\n");
		WSACleanup();
		return 0;
	}
	else
	{
		printf("You Can start sending and receiving data...\n");	
	}
	int bytesSent;
	int bytesRecv;

	while(1){
here:while( 2)
		{
			
			char leave[]="end";
			printf("\t\t%s\n","To login, Enter YOUR \nUsername \t password\n");
			char sendbuf[200]="";
			//scanf("%[^\n]",sendbuf);
			//sendig username and password to server
			gets(sendbuf);
			bytesSent=send(m_socket , sendbuf , strlen(sendbuf) , 0);//...........RECIEVE FROM SERVER
			if (bytesSent == SOCKET_ERROR){
				printf("client: send() error %ld.\n", WSAGetLastError());
				WSACleanup();
				return 0;
			}
			
			//when entere is typed
			if(strcmp(sendbuf,"\0")==0){
				printf("Enter actuall values\n");
				goto here;
			}
			
			//to log out
			else if(strcmp(sendbuf,leave)==0){
				printf("Client closed\n");
				WSACleanup();
				return 0;
			}
			/*else{
				printf("client: send() is OK. Bytes Sent: %ld.\n",bytesSent);
			}*/
			
			
			
			//To  receive and validate login details
			char recvbuf[100]="";
			bytesRecv = recv(m_socket, recvbuf, 200, 0);//...........RECIEVE FROM SERVER
			if (bytesRecv == SOCKET_ERROR){
				printf("client: recv() error %ld.\n", WSAGetLastError());
				break;
			}
			else
			{
				printf("\"%s\"\n", recvbuf);
				
				char username1[30]="";
				char logindetailsfromserver[60]="";
				char usernmeandpasswrd[50]="";
				char yourname[30]="";
				strcpy(usernmeandpasswrd,sendbuf);
				strcpy(yourname,strtok(usernmeandpasswrd," "));
				strcpy(logindetailsfromserver,recvbuf);
				strcpy(username1,strtok(logindetailsfromserver," "));

				//if username1 exists on the system
				if(strcmp(yourname,username1)==0){
					
					printf("%s","\n\n\t\t\tType these commands as follows:\ncontribution amount date person_name receipt_number\tTo submit a contribution\n\ncontribution check                                 \tto see how much has been contributed\n\nbenefits check                                     \tTo see how much has been received in benefits only\n\nloan request amount                                \trequest for loan\n\nloan status                                        \tcheck loan status (Approved, denied or pending)\n\nload repayment_details                             \tcheck the loan repayment details ie which amounts and how much per month\n\nidea name capital \"simple description\" \n");
					
					
LOOP:while(3){
						char therow1[]="stop";
						char leave[]="logout";
						int bytesRecv1;
						int bytesSent1;
						char sendbuf1[200]="";
						//scanf("%[^\n]",sendbuf);
						printf("\nEnter command\n");
						gets(sendbuf1);
						bytesSent1=send(m_socket , sendbuf1 , strlen(sendbuf1) , 0);//...........RECIEVE FROM SERVER
						if (bytesSent1 == SOCKET_ERROR){
							printf("client: send() error %ld.\n", WSAGetLastError());
							break;
						}
						
						
						
						//To logout
						if(strncmp(sendbuf1,leave,6)==0){	
							printf("you have loged out\n");
							break;
						}
						
						
						//when a new line is entered
						if(strcmp(sendbuf1,"\0")==0){
							printf("Enter actuall values\n");
							goto LOOP;
						}
						/*else
						{
							printf("client: send() is OK. Bytes Sent: %ld.\n",bytesSent1);
						}*/
						

						//Requests that involve data from the database
						if((strncmp(sendbuf1,stoname1,18)==0)||(strncmp(sendbuf1,stoname2,14)==0)||(strncmp(sendbuf1,stoname3,11)==0)||(strncmp(sendbuf1,stoname4,22)==0)){
							while(1){
								char recvbuf1[250]="";
								bytesRecv1 = recv(m_socket, recvbuf1, 250, 0);//...........RECIEVE FROM SERVER
								printf("%s", recvbuf1);
								if (bytesRecv1== SOCKET_ERROR){
									printf("client: recv() error %ld.\n", WSAGetLastError());
									break;
								}
								else if(strstr(recvbuf1,therow1)!=NULL)
								{								
									goto LOOP;
								}
								//
							}	
						}
						
						
						
						//To cater for results of any other command sent to the server
						else{
							int bytesRecv2;
							char recvbuf2[250]="";
							bytesRecv2= recv(m_socket, recvbuf2, 250, 0);//...........RECIEVE FROM SERVER

							if (bytesRecv2== SOCKET_ERROR){
								printf("client: recv() error %ld.\n", WSAGetLastError());
								break;
							}
							else
							{

								printf("%s\n", recvbuf2);
							}
						}
					}
				}	
			}
		}
	}
		return 0;
}