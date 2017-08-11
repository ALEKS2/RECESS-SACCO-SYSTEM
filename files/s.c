//server program
#include <winsock2.h>
#include <winsock.h>
#include <mysql.h>
#include <stdio.h>
#include<stdlib.h>
#include<string.h>
#pragma comment(lib,"ws2_32.lib") //Winsock Library
void finish_with_error(MYSQL *con)
{  fprintf(stderr, "%s\n", mysql_error(con));
	mysql_close(con);
	exit(1);        
}int ephraim(char called[50]);
char oburu(char name[50]);
int main()
{	
	char sendbuf15[] ="Please type command correctly &Leave not more than one spacing in the command";
	char sendbuf14[] ="ENDED";
	char sendbuf13[] ="the command is incomplete";
	char sendbuf12[] ="capital should be a figure";
	char sendbuf11[] ="rightly typed idea";
	char sendbuf10[] ="incomplete command";
	char sendbuf9[] ="Type a number for ammount";
	char sendbuf8[] ="right contribuution command";
	char sendbuf7[] ="incomplete command";
	char sendbuf6[] ="ammount should be a figure";
	char sendbuf5[] ="right loan request,amount requested";
	char sendbuf16[] ="right loan pay command";
	char sendbuf4[] ="your about to check your loan repayment_details the command is right";
	char sendbuf3[] ="your about to check your loan status because the command is right";
	char sendbuf2[] ="your about to check your benefits because the command is right";
	char sendbuf1[] ="your about to check your contribution because the command is right";
	char name[200],name1[200],command1[20],command2[20],command3[20],command4[20],ammount[20];
	char check[]="end";
	char stoname5[]="loan request",stoname6[]="contribution",stoname7[]="idea",stoname8[]="payloan";
	char description[200],projectname[30],capital[20];
	float f;
	int words;
	FILE *fptr;
	int bytesRecv;
	int bytesSent;
	int c;
	char clientlog[]="logout";
	char stoname1[]="contribution check",stoname2[]="benefits check",stoname3[]="loan status",stoname4[]="loan repayment_details";
	char n[]="\n";
	WORD wVersionRequested;
	WSADATA wsaData;
	int wsaerr;
	wVersionRequested = MAKEWORD(2, 2);
	wsaerr = WSAStartup(wVersionRequested, &wsaData);
	if (wsaerr != 0)
	{
		printf("Server: The Winsock dll not found!\n");
		return 0;
	}

	if (LOBYTE(wsaData.wVersion) != 2 || HIBYTE(wsaData.wVersion) != 2 )
	{
		printf("The dll do not support the Winsock version %u.%u!\n", LOBYTE(wsaData.wVersion), HIBYTE(wsaData.wVersion));
		WSACleanup();
		return 0;
	}

	
	SOCKET m_socket;
	m_socket = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);
	if (m_socket == INVALID_SOCKET)
	{
		printf("Server: Error at socket(): %ld\n", WSAGetLastError());
		WSACleanup();
		return 0;
	}

	struct sockaddr_in service;
	service.sin_family = AF_INET;
	service.sin_addr.s_addr = inet_addr("127.0.0.1");
	service.sin_port = htons(55555);
	if (bind(m_socket, (SOCKADDR*)&service, sizeof(service)) == SOCKET_ERROR)
	{
		printf("Server: bind() failed: %ld.\n", WSAGetLastError());
		closesocket(m_socket);
		return 0;
	}

	if (listen(m_socket, 10) == SOCKET_ERROR){
		printf("Server: listen(): Error listening on socket %ld.\n", WSAGetLastError());
		WSACleanup();
		return 0;
	}
	printf("Server is ready...Open your client program\n");
	c = sizeof(struct sockaddr_in);	
	while( (m_socket = accept(m_socket , (struct sockaddr *)&service,&c)) != INVALID_SOCKET )
	{
		

LOOP:while(1){
			while(2){
				MYSQL *con = mysql_init(NULL);
				if (con == NULL)
				{
					fprintf(stderr, "mysql_init() failed\n");
					exit(1);
				}  
				if (mysql_real_connect(con, "localhost", "root", "", 
							"sacco", 0, NULL, 0) == NULL) 
				{
					finish_with_error(con);
				}
				
				if (mysql_query(con, "SELECT username FROM member")) 
				{
					finish_with_error(con);
				}
				MYSQL_RES *result1 = mysql_store_result(con);
				if (result1 == NULL) 
				{
					finish_with_error(con);
				}
				int num_fields1 = mysql_num_fields(result1);
				MYSQL_ROW row1;
				

				
				printf("Login\n");
				char recvbuf[200] ="";
				bytesRecv = recv(m_socket, recvbuf, 200, 0);
				if (bytesRecv == SOCKET_ERROR){
					printf("Server: recv() error %ld.\n", WSAGetLastError());
					WSACleanup();
					return 0;
				}
				else if(strcmp(recvbuf,check)!=0)
				{
					printf("Server: recv() is OK. Bytes received: %ld.\n",bytesRecv);
					printf("Server: Received data is: \"%s\"\n", recvbuf);
					//FOR THE LOGIN
					char new[100]="";
					char user[30]="";
					char pass[30]="";
					int b;
					words=ephraim(recvbuf);
					if(words==2){
						
						
						
						
						
						
						
						strcpy(new,recvbuf);
						strcpy(user,strtok(new," "));
						strcpy(pass,strtok(NULL," "));
						
						
						
						
						while((row1 = mysql_fetch_row(result1))) 
						{ 
							
							//while(1){
							for(int i = 0; i < num_fields1; i++) 
							{ 
								char usr[80];
								if(strcmp(row1[i],user)==0){
									printf("username exists in the database\n");
									b=i;
									
									
									char queryStr2[300];
									sprintf(queryStr2,"SELECT password FROM member WHERE username='%s';",user);
									if (mysql_query(con,queryStr2)) 
									{
										finish_with_error(con);
									}
									MYSQL_RES *result2 = mysql_store_result(con);
									if (result2 == NULL) 
									{
										finish_with_error(con);
									}
									int num_fields2 = mysql_num_fields(result2);
									MYSQL_ROW row2;
									
									
									
									
									
									
									
									while((row2 = mysql_fetch_row(result2))) { 
										if (strcmp(row2[0],pass)==0){
											printf("password exists in the database\n");
											sprintf(usr,"%s welcome ,your loged in",user);
											//char login[40]="";
											
											bytesSent=send(m_socket ,usr , strlen(usr) , 0);//.......SENT TO CLIENT
											if (bytesSent == SOCKET_ERROR){
												printf("Server: send() error %ld.\n", WSAGetLastError());
											}
											else{
												printf("Server: send() is OK.\n");
												printf("Server: Bytes Sent1: %ld.\n", bytesSent);
											}
											
											
											
											
											
											
											while(3){
												
												
												
												
												
												
												
												
												
												
												
												
												char therow1[]="stop";
												int m=0;
												char k[]="by";
												int bytesRecv1;
												char recvbuf1[200] ="";
												bytesRecv1 = recv(m_socket, recvbuf1, 200, 0);
												if (bytesRecv1 == SOCKET_ERROR){
													printf("Server: recv() error %ld.\n", WSAGetLastError());
													WSACleanup();
													return 0;
													
												}
												if(strcmp(recvbuf1,check)==0){
													printf("Server: recv() is OK.\n");
													printf("Server: Received data is: \"%s\"\n", recvbuf1);
													puts("server closed");
													WSACleanup();
													return 0;
												}
												
												strcpy(name,recvbuf1);

												
												
												
												
												//CONTRIBUTION CHECK
												if(strcmp(name,stoname1)==0){
													char queryStr[300];
													sprintf(queryStr,
													"SELECT * FROM contribution WHERE username='%s';",user);
													if (mysql_query(con, queryStr)) 
													{
														finish_with_error(con);
													}
													MYSQL_RES *result = mysql_store_result(con);
													if (result == NULL) 
													{
														finish_with_error(con);
													}
													int num_fields = mysql_num_fields(result);
													int num_rows = mysql_num_rows(result);
													MYSQL_ROW row;
													
													//retrieving to display query reult to client
													while ((row = mysql_fetch_row(result))) 
													{ 
														if(num_rows<1){
															bytesSent=send(m_socket ,therow1, strlen(therow1) , 0);
															mysql_free_result(result);	
															break;
														}														

														
														//retrieving each fieled from the rows
														for(int i = 1; i < num_fields -1; i++) 
														{
															char therow[15]="";

															sprintf(therow,"%s ", row[i] ? row[i] : "NULL");
															bytesSent=send(m_socket ,therow , strlen(therow) , 0);//.......SENT TO CLIENT
														} 
														//when the rows to be retrived from database are done
														if(m+1==num_rows){
															bytesSent=send(m_socket ,therow1, strlen(therow1) , 0);
															mysql_free_result(result);	
															break;
														}
														
														bytesSent=send(m_socket ,n, strlen(n) , 0);//.......SENT TO CLIENT
														//printf("\n");
														m++;
													}
													
												}
												
												
												
												
												//COMMAND 2 CHECKING  BENEFITS
												else if(strcmp(name,stoname2)==0){
													printf("%s",sendbuf2);
													char queryStr1[300];
													sprintf(queryStr1,"SELECT * FROM benefits WHERE username='%s';",user);
													if (mysql_query(con, queryStr1)) 
													//if (mysql_query(con, "SELECT * FROM contribution WHERE username='%s';",user)) 
													
													{
														finish_with_error(con);
													}
													MYSQL_RES *result3 = mysql_store_result(con);
													if (result3 == NULL) 
													{
														finish_with_error(con);
													}
													
													int num_fields3 = mysql_num_fields(result3);
													int num_rows3= mysql_num_rows(result3);
													MYSQL_ROW row3;
													while ((row3 = mysql_fetch_row(result3))) 
													{
														if(num_rows3<1){
															bytesSent=send(m_socket ,therow1, strlen(therow1) , 0);
															mysql_free_result(result3);	
															break;
														}

														//retrieving each fieled from the rows
														for(int i = 1; i < num_fields3-1; i++) 
														{
															char therow2[15]="";
															//printf("%s ", row[i] ? row[i] : "NULL");
															sprintf(therow2,"%s ", row3[i] ? row3[i] : "NULL");
															bytesSent=send(m_socket ,therow2, strlen(therow2) , 0);//.......SENT TO CLIENT
														} 
														//when the rows to be retrived from database are done														
														if(m+1==num_rows3){
															bytesSent=send(m_socket ,therow1, strlen(therow1) , 0);
															mysql_free_result(result3);		
															break;
														}
														bytesSent=send(m_socket ,n, strlen(n) , 0);//.......SENT TO CLIENT
														m++;
													}
													
													
												}
												
												
												
												
												
												
												
												//COMMAND 3 CHECKING LOAN STATUS
												else if(strcmp(name,stoname3)==0){
													
													char queryStr2[300];
													sprintf(queryStr2,
													"SELECT * FROM loan WHERE username='%s';",user);
													if (mysql_query(con, queryStr2)) 
													//if (mysql_query(con, "SELECT * FROM contribution WHERE username='%s';",user)) 
													
													{
														finish_with_error(con);
													}
													MYSQL_RES *result4 = mysql_store_result(con);
													if (result4 == NULL) 
													{
														finish_with_error(con);
													}
													int num_fields4 = mysql_num_fields(result4);
													int num_rows4 = mysql_num_fields(result4);
													MYSQL_ROW row4;
													while ((row4 = mysql_fetch_row(result4))) 
													{
														if(num_rows4<1){
															bytesSent=send(m_socket ,therow1, strlen(therow1) , 0);
															mysql_free_result(result4);	
															break;
														}

														//retrieving each fieled from the rows
														for(int i = 1; i < num_fields4-1; i++) 
														{
															
															char therow3[15]="";
															//printf("%s ", row[i] ? row[i] : "NULL");
															sprintf(therow3,"%s ", row4[i] ? row4[i] : "NULL");
															bytesSent=send(m_socket ,therow3 , strlen(therow3) , 0);//.......SENT TO CLIENT
														} 
														bytesSent=send(m_socket ,n, strlen(n) , 0);//.......SENT TO CLIENT
														//when the rows to be retrived from database are done
														if(m+1==num_rows4){
															bytesSent=send(m_socket ,therow1, strlen(therow1) , 0);
															mysql_free_result(result4);			
															break;
														}
														bytesSent=send(m_socket ,n, strlen(n) , 0);//.......SENT TO CLIENT
														m++;
													}
												}
												
												
												
												
												
												
												//COMMAND 4 CHECKING LOAN REPAYMENT DEATILS
												else if(strcmp(name,stoname4)==0){
													char queryStr3[300];
													sprintf(queryStr3,
													"SELECT * FROM loan_transactions WHERE username='%s';",user);
													if (mysql_query(con, queryStr3)) 
													//if (mysql_query(con, "SELECT * FROM contribution WHERE username='%s';",user)) 
													
													{
														finish_with_error(con);
													}
													MYSQL_RES *result5 = mysql_store_result(con);
													if (result5 == NULL) 
													{
														finish_with_error(con);
													}
													int num_fields5 = mysql_num_fields(result5);
													int num_rows5 = mysql_num_fields(result5);
													MYSQL_ROW row5;
													while ((row5 = mysql_fetch_row(result5))) 
													{
														
														if(num_rows5<1){
															bytesSent=send(m_socket ,therow1, strlen(therow1) , 0);
															mysql_free_result(result5);	
															break;
														}

														//retrieving each fieled from the rows
														for(int i = 1; i < num_fields5-1; i++) 
														{
															char therow4[15]="";
															//printf("%s ", row[i] ? row[i] : "NULL");
															sprintf(therow4,"%s ", row5[i] ? row5[i] : "NULL");
															bytesSent=send(m_socket ,therow4 , strlen(therow4) , 0);//.......SENT TO CLIENT
														} 
														//when the rows to be retrived from database are done
														if(m+1==num_rows5){
															bytesSent=send(m_socket ,therow1, strlen(therow1) , 0);
															mysql_free_result(result5);			
															break;
														}
														bytesSent=send(m_socket ,n, strlen(n) , 0);//.......SENT TO CLIENT
														m++;
													}
													
												}

												
												
												
												
												else if(strncmp(name,stoname8,7)==0){
													//COPYING ORIGINAL STRING TO name1
													strcpy(name1,name);
													//COUNTING WORDS IN ORIGINAL STRING
													words=ephraim(name);
													if (words>1){
														//TOKENISING AND COPYING THE STRING
														strcpy(command1, strtok(name1," "));
														strcpy(ammount, strtok(NULL," "));
														//printf("%s %s\n",command1,ammount);
														//CONVERTING STRING TO A NUMBER
														char k[]="by";
														f=strtof(ammount,NULL);
														if(f>0.000000){
															bytesSent=send(m_socket , sendbuf16, strlen(sendbuf16) , 0);//......SENT TO CLIENT
															if (bytesSent == SOCKET_ERROR){
																printf("Server: send() error %ld.\n", WSAGetLastError());
															}
															else{
																printf("Server: send() is OK.\n");
																printf("Server: Bytes Sent2: %ld.\n", bytesSent);
															}
															//WRITING TO THE END OF THE FILE
															fptr=fopen("serverfile.txt","a");
															
															fprintf(fptr,"\n%s %f",command1,f);fprintf(fptr," %s",k);
															fprintf(fptr," %s",user);
															fclose(fptr);
														}
														//WHEN AMMOUNT IS NOT A NUMBER
														else{
															bytesSent=send(m_socket , sendbuf6 , strlen(sendbuf6) , 0);//.......SENT TO CLIENT
															if (bytesSent == SOCKET_ERROR){
																printf("Server: send() error %ld.\n", WSAGetLastError());
															}
															else{
																printf("Server: send() is OK.\n");
																printf("Server: Bytes Sent3: %ld.\n", bytesSent);
															}
														}
													}
													//ERROR INCOMPLETE COMMAND
													else{
														bytesSent=send(m_socket , sendbuf7 , strlen(sendbuf7) , 0);//.......SENT TO CLIENT
														if (bytesSent == SOCKET_ERROR){
															printf("Server: send() error %ld.\n", WSAGetLastError());
														}
														else{
															printf("Server: send() is OK.\n");
															printf("Server: Bytes Sent4: %ld.\n", bytesSent);
														}
														
													}
												}
												
												
												
												
												
												
												
												
												//COMMAND 5 LOAN REQUEST
												else if(strncmp(name,stoname5,12 )==0){
													//COPYING ORIGINAL STRING TO name1
													strcpy(name1,name);
													//COUNTING WORDS IN ORIGINAL STRING
													words=ephraim(name);
													if (words>2){
														//TOKENISING AND COPYING THE STRING
														strcpy(command1, strtok(name1," "));
														strcpy(command2, strtok(NULL," "));
														strcpy(ammount, strtok(NULL," "));
														printf("%s %s %s\n",command1,command2,ammount);
														//CONVERTING STRING TO A NUMBER
														char k[]="by";
														f=strtof(ammount,NULL);
														if(f>0.000000){
															bytesSent=send(m_socket , sendbuf5 , strlen(sendbuf5) , 0);//......SENT TO CLIENT
															if (bytesSent == SOCKET_ERROR){
																printf("Server: send() error %ld.\n", WSAGetLastError());
															}
															else{
																printf("Server: send() is OK.\n");
																printf("Server: Bytes Sent2: %ld.\n", bytesSent);
															}
															//WRITING TO THE END OF THE FILE
															fptr=fopen("serverfile.txt","a");
															
															fprintf(fptr,"\n%s %s %f",command1,command2,f);fprintf(fptr," %s",k);
															fprintf(fptr," %s",user);
															fclose(fptr);
														}
														//WHEN AMMOUNT IS NOT A NUMBER
														else{
															bytesSent=send(m_socket , sendbuf6 , strlen(sendbuf6) , 0);//.......SENT TO CLIENT
															if (bytesSent == SOCKET_ERROR){
																printf("Server: send() error %ld.\n", WSAGetLastError());
															}
															else{
																printf("Server: send() is OK.\n");
																printf("Server: Bytes Sent3: %ld.\n", bytesSent);
															}
														}
													}
													//ERROR INCOMPLETE COMMAND
													else{
														bytesSent=send(m_socket , sendbuf7 , strlen(sendbuf7) , 0);//.......SENT TO CLIENT
														if (bytesSent == SOCKET_ERROR){
															printf("Server: send() error %ld.\n", WSAGetLastError());
														}
														else{
															printf("Server: send() is OK.\n");
															printf("Server: Bytes Sent4: %ld.\n", bytesSent);
														}
														
													}
												}
												
												//COMMAND 6 MAKING A CONTRIBUTION
												else if(strncmp(name,stoname6,12)==0){
													strcpy(name1,name);
													words=ephraim(name);
													if (words>4){
														strcpy(command1, strtok(name1," "));
														strcpy(ammount, strtok(NULL," "));
														strcpy(command2, strtok(NULL," "));
														strcpy(command3, strtok(NULL," "));
														strcpy(command4, strtok(NULL," "));
														//CONVERTING STRING TO A NUMBER
														f=strtof(ammount,NULL);
														if(f>0.000000 ){
															bytesSent=send(m_socket , sendbuf8 , strlen(sendbuf8) , 0);//.......SENT TO CLIENT
															if (bytesSent == SOCKET_ERROR){
																printf("Server: send() error %ld.\n", WSAGetLastError());
															}
															else{
																printf("Server: send() is OK.\n");
																printf("Server: Bytes Sent5: %ld.\n", bytesSent);
															}
															//WRITING TO THE END OF THE FILE
															fptr=fopen("serverfile.txt","a");
															fprintf(fptr,"\n%s %f %s %s %s ",command1,f,command2,command3,command4);
															fclose(fptr);
														}
														//INCASE AMMOUNT IS NOT A FIGURE
														else{
															bytesSent=send(m_socket , sendbuf9 , strlen(sendbuf9) , 0);//.......SENT TO CLIENT
															if (bytesSent == SOCKET_ERROR){
																printf("Server: send() error %ld.\n", WSAGetLastError());
															}
															else{
																printf("Server: send() is OK.\n");
																printf("Server: Bytes Sent6: %ld.\n", bytesSent);
															}
														}
													}
													//ERROR INCOMPLETE COMMAND
													else{
														bytesSent=send(m_socket , sendbuf7, strlen(sendbuf7) , 0);//.......SENT TO CLIENT
														if (bytesSent == SOCKET_ERROR){
															printf("Server: send() error %ld.\n", WSAGetLastError());
														}
														else{
															printf("Server: send() is OK.\n");
															printf("Server: Bytes Sent7: %ld.\n", bytesSent);
														}
													}
												}
												
												//COMMAND 7 ENTERING IDEA
												else if(strncmp(name,stoname7,4)==0){
													strcpy(name1,name);
													words=ephraim(name);
													char *tokenPtr;
													if (words>3){
														strcpy(command1, tokenPtr=strtok(name1," "));
														strcpy(projectname, strtok(NULL," "));
														strcpy(capital, strtok(NULL," "));
														
														//CONVERTING STRING TO A NUMBER
														f=strtof(capital,NULL);
														if(f!=0.000000){
															bytesSent=send(m_socket , sendbuf11 , strlen(sendbuf11) , 0);//.......SENT TO CLIENT
															if (bytesSent == SOCKET_ERROR){
																printf("Server: send() error %ld.\n", WSAGetLastError());
															}
															else{
																printf("Server: send() is OK.\n");
																printf("Server: Bytes Sent8: %ld.\n", bytesSent);
															}
															//WRITING TO THE END OF THE FILE
															fptr=fopen("serverfile.txt","a");
															fprintf(fptr,"\n%s %s %f ",command1,projectname,f);
															tokenPtr = strtok(NULL, " ");
															while(tokenPtr != NULL)
															{
																//tokenPtr = strtok(NULL, " \t\r\n\f");
																
																fprintf(fptr,"%s ",tokenPtr);
																tokenPtr = strtok(NULL, " ");
															}
															fprintf(fptr," %s",k);fprintf(fptr," %s",user);
															fclose(fptr);
														}
														//ERROR MESSAGE : CAPITAL SHOULD BE A FIGURE
														else{
															bytesSent=send(m_socket , sendbuf12 , strlen(sendbuf12) , 0);//.......SENT TO CLIENT
															if (bytesSent == SOCKET_ERROR){
																printf("Server: send() error %ld.\n", WSAGetLastError());
															}
															else{
																printf("Server: send() is OK.\n");
																printf("Server: Bytes Sent9: %ld.\n", bytesSent);
															}
														}
													}
													//ERROR INCOMPLETE COMMAND
													else{
														bytesSent=send(m_socket , sendbuf13, strlen(sendbuf13) , 0);//.......SENT TO CLIENT
														if (bytesSent == SOCKET_ERROR){
															printf("Server: send() error %ld.\n", WSAGetLastError());
														}
														else{
															printf("Server: send() is OK.\n");
															printf("Server: Bytes Sent10: %ld.\n", bytesSent);
														}
													}
												}
												
												
												
												
												//when user logs out
												else if(strcmp(name,clientlog)==0){
													printf("client loged out\n");
													bytesSent=send(m_socket , clientlog , strlen(clientlog) , 0);//.......SENT TO CLIENT
													if (bytesSent == SOCKET_ERROR){
														printf("Server: send() error %ld.\n", WSAGetLastError());
													}
													else{
														printf("Server: send() is OK.\n");
														printf("Server: Bytes Sent12: %ld.\n", bytesSent);
														break;
													}
													
												}
												
												
												//ERROR MESSAGE:TYPE COMMAND CORRECTLY
												else{
													char correctly[]="k";
													bytesSent=send(m_socket ,correctly, strlen(correctly) , 0);//.......SENT TO CLIENT
													if (bytesSent == SOCKET_ERROR){
														printf("Server: send() error %ld.\n", WSAGetLastError());
													}
													else{
														printf("Server: send() is OK.\n");
														printf("Server: Bytes Sent12: %ld.\n", bytesSent);
													}
												}

												
												
												
												
												
												
												
												
												
												
												
												
												
												
												
												
												
												
												
												
												
												
												
												
												
												
												
											}//2
											
											
											
											
										}
										
										
										
										

										
										
										
										
									}
									
									char wrongpass[]="your password is wrong";
									bytesSent=send(m_socket , wrongpass , strlen(wrongpass) , 0);//.......SENT TO CLIENT
									if (bytesSent == SOCKET_ERROR){
										printf("Server: send() error %ld.\n", WSAGetLastError());
									}
									else{
										printf("Server: send() is OK.\n");
										printf("Server: Bytes14 Sent: %ld.\n", bytesSent);
										
										mysql_free_result(result2);	
										mysql_close(con);
										goto LOOP;
									}
									
								}
								
								//printf();
								/*else{
								char wrongpass1[]="your username is wrong";
								bytesSent=send(m_socket , wrongpass1 , strlen(wrongpass1) , 0);//.......SENT TO CLIENT
								if (bytesSent == SOCKET_ERROR){
									printf("Server: send() error %ld.\n", WSAGetLastError());
								}
								else{
									printf("Server: send() is OK.\n");
									printf("Server: Bytes14 Sent: %ld.\n", bytesSent);
								}
								break;
							}*/
								
								
							}
							//}
							
							
							
							
						}//while	
						
						
						
						char wrongpass1[]="your username is wrong";
						bytesSent=send(m_socket , wrongpass1 , strlen(wrongpass1) , 0);//.......SENT TO CLIENT
						if (bytesSent == SOCKET_ERROR){
							printf("Server: send() error %ld.\n", WSAGetLastError());
						}
						else{
							printf("Server: send() is OK.\n");
							printf("Server: Bytes14 Sent: %ld.\n", bytesSent);

							mysql_free_result(result1);	
							mysql_close(con);
							break;
						}
						
						
						
						
					}//one word
					else{
						char wrongpass2[]="Enter both username and password";
						bytesSent=send(m_socket , wrongpass2 , strlen(wrongpass2) , 0);//.......SENT TO CLIENT
						if (bytesSent == SOCKET_ERROR){
							printf("Server: send() error %ld.\n", WSAGetLastError());
							break;
						}
						else{
							printf("Server: send() is OK.\n");
							printf("Server: Bytes14 Sent: %ld.\n", bytesSent);
						}
						break;
					}
					
					
					
					
				}
				
				
				
				
				
				else{
					printf("Server: recv() is OK.\n");
					printf("Server: Received data is: \"%s\"\n", recvbuf);
					puts("server closed");
					WSACleanup();
					return 0;
				}
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				
				

			}//row1
		}
		
		
		
	}
	//IF SOCKET IS NOT ACCEPTED IN THE WHILE LOOP
	if (m_socket == INVALID_SOCKET)
	{
		printf("accept failed with error code : %d" , WSAGetLastError());
		return 1;
	}
	WSACleanup();
	return 0;
}int ephraim(char called[200])
{	
	int i, count = 0;
	char *delim=" \t\r\n\f";
	char *ptok;
	char name2[200];
	strcpy(name2,called);
	ptok = strtok (name2, delim);
	/* while there is still a token, */
	while (ptok != NULL)
	{
		/* if token is not empty, count it. */
		if (strlen (ptok) > 0)
		count++;
		/* Find the next token. */
		ptok = strtok (NULL, delim);
	}	
	return count;
}