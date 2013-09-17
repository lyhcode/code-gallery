/*         PCzone Web Site         */
/* http://pczone.tw.to             */
/* pcgamer@mail2.intellect.com.tw  */

#include<stdio.h>
#include<stdlib.h>
#include<graphics.h>
#include<conio.h>
#include<time.h>
#include<dos.h>
#define GAME_MAXLEVEL 99
#define COLOR_BACK WHITE
#define COLOR_USER YELLOW
#define COLOR_FOOD RED
#define COLOR_SHIT BROWN
#define COLOR_ROCK LIGHTGRAY
#define COLOR_GOLD 88
#define COLOR_NONE BLACK
#define CLEAR_BACK 0
#define CLEAR_USER 1
#define GAME_EXIT 0
#define GAME_INIT 1
#define GAME_RUN 2
#define GAME_OVER 3
#define GAME_MENU 4
#define GAME_WIN 5
#define GAME_WAIT 6
#define KEY_ENTER 13
#define KEY_ESC 27
#define KEY_RIGHT 77
#define KEY_LEFT 75
#define KEY_UP 72
#define KEY_DOWN 80
#define KEY_SPACEBAR 32
#define TURN_RIGHT 0
#define TURN_LEFT 1
#define TURN_UP 2
#define TURN_DOWN 3
#define ACT_OVER 1
#define MSG_GAMEOVER "     Game Over !     "
#define ACT_WIN 2
#define MSG_GAMEWIN "    You are win !    "


int GameState;            //--> Game State Flag
int KeyIn=0,level=0;              //--> Keyboard Input Get
short int screen[64][48]; //--> Screen Recorder Array
char far *bb,*bu,*bf,*bs,*bg,*bc,*br,*gtmp;     //--> Buffer of Images
//--<bb>--> BACK
//--<bu>--> USER
//--<bf>--> FOOD
//--<bs>--> SHIT
//--<bg>--> GOLD
//--<br>--> ROCK
clock_t start_t,end_t;    //--> Time Recoder
unsigned long int Score;           //--> Score
short int MAXLEVEL;              //--> Record the Max Level Num
short int StartX,StartY,direction;
unsigned int MaxFood;
float speed;

void getImage(void);                              //-->Get Image Buffers
void putImage(short,short,short);     //-->Draw Image to Screen
void displayScore(void);
void msgbox(short);
void buildBack(void);
void buildMenu(void);
void levelDisplay(void);
void readLevelData(short);
void getMaxLevel(void);

struct list {
  short x;
  short y;
  struct list *back;
  struct list *next;
};
typedef struct list node;
typedef node *snake;

snake head,ptr,tmp;

void main(void) {
  int gdriver = VGA, gmode = VGAHI;
  int i,s;
  short repeat,tmpX,tmpY,keyin;
  //--> Repeat is used for Snake Become Long


  printf("Loading Data Now....");

  getMaxLevel();

  printf("\n\nThe Hungry Snake 2000....Okay....");
  printf("\n\nVersion 1.11 beta...Okay....");
  printf("\n\nThe maximum level number is [%i] !!",MAXLEVEL);
  printf("\n\nPress any key to continue....");

  if (getch()==0) getch();

  initgraph(&gdriver, &gmode, "d:\\tc\\bgi");

  getImage();

  GameState=GAME_MENU;

  buildMenu();

  while (GameState!=GAME_EXIT) {

    switch (GameState) {
      case GAME_RUN:
	   {
	   if (kbhit()) {

	     keyin=getch();

	     if (keyin==0) keyin=getch();

	     switch (keyin)
	       {
	       case KEY_ESC:
		    {
		    GameState=GAME_OVER;
		    } break;
	       case KEY_LEFT:
		    {
		    direction=TURN_LEFT;
		    } break;
	       case KEY_RIGHT:
		    {
		    direction=TURN_RIGHT;
		    } break;
	       case KEY_UP:
		    {
		    direction=TURN_UP;
		    } break;
	       case KEY_DOWN:
		    {
		    direction=TURN_DOWN;
		    } break;
	       case KEY_SPACEBAR:
		    {
		    GameState=GAME_WAIT;
		    } break;
	       default:
		    {
		    } break;
	       }
	   }

	   end_t=clock();

	   if ((end_t-start_t)/CLK_TCK<speed) break;
	   else start_t=clock();

	   ptr=head;
	   while (ptr!=NULL) {
	     if (ptr->next!=NULL) {
	       ptr=ptr->next;
	       continue;
	     }
	     putImage(ptr->x,ptr->y,CLEAR_USER);
	     ptr=ptr->next;
	   }

	   tmpX=head->x;
	   tmpY=head->y;
	   switch (direction)
	     {
	     case TURN_RIGHT:
		  head->x++;
		  break;
	     case TURN_LEFT:
		  head->x--;
		  break;
	     case TURN_UP:
		  head->y--;
		  break;
	     case TURN_DOWN:
		  head->y++;
		  break;
	     }

	   ptr=head;

	   while (ptr->next!=NULL)
	     {
	     ptr=ptr->next;
	     }

	   while (ptr->back!=NULL)
	     {
	     if (ptr->back->back==NULL)
	       {
	       ptr->x=tmpX;
	       ptr->y=tmpY;
	       break;
	       }
	     else
	       {
	       ptr->x=ptr->back->x;
	       ptr->y=ptr->back->y;
	       }
	     ptr=ptr->back;
	     }

	   switch (screen[head->x][head->y])
	     {
	     case COLOR_NONE:
		  break;

	     case COLOR_SHIT:
		  Score-=30;
		  screen[head->x][head->y]=COLOR_NONE;
		  displayScore();
		  sound(100);
		  delay(20);
		  nosound();
		  sound(300);
		  delay(60);
		  nosound();
		  break;

	     case COLOR_FOOD:
		  Score+=50;
		  screen[head->x][head->y]=COLOR_NONE;
		  displayScore();
		  repeat++;
		  MaxFood--;
		  sound(1200);
		  delay(15);
		  nosound();
		  if (MaxFood<=0) GameState=GAME_WIN;
		  break;

	     case COLOR_GOLD:
		  Score+=200;
		  screen[head->x][head->y]=COLOR_NONE;
		  displayScore();
		  sound(1000);
		  delay(30);
		  nosound();
		  sound(200);
		  delay(30);
		  nosound();
		  break;

	     case COLOR_BACK:
		  GameState=GAME_OVER;
		  break;

	     case COLOR_ROCK:
		  GameState=GAME_OVER;
		  break;

	     case COLOR_USER:
		  GameState=GAME_OVER;
		  break;
	     }
	   if (GameState==GAME_OVER) break;


	   ptr=head;
	   while (ptr!=NULL)
	     {

/* Because didn't have different! */
/*
	     if (screen[ptr->x][ptr->y]==COLOR_USER)
	       {
	       ptr=ptr->next;
	       continue;
	       }
*/
	     putImage(ptr->x,ptr->y,COLOR_USER);
	     ptr=ptr->next;
	     }

	   ptr=head;

	   while (ptr->next!=NULL)
	     {
	     ptr=ptr->next;
	     }

	   if (repeat>0)
	     {
	     repeat--;

	     tmp=(snake) malloc(sizeof(node));
	     tmp->x=ptr->x;
	     tmp->y=ptr->y;
	     tmp->next=NULL;
	     tmp->back=ptr;
	     ptr->next=tmp;
	     ptr=tmp;
	     }
	   } break;

      case GAME_MENU:
	   {
	   levelDisplay();
	   switch (getch())
	     {
	     case KEY_ENTER:
		  {
		  GameState=GAME_INIT;
		  } break;
	     case KEY_ESC:
		  {
		  GameState=GAME_EXIT;
		  } break;
	     case KEY_LEFT:
		  {
		  level--;
		  if (level<0) level=MAXLEVEL;
		  } break;
	     case KEY_RIGHT:
		  {
		  level++;
		  if (level>MAXLEVEL) level=0;
		  } break;
	     }
	   } break;

      case GAME_INIT:
	   {
	   buildBack();
	   readLevelData(level);
	   Score=0;
	   repeat=0;
	   head=(snake) malloc(sizeof(node));
	   head->next=NULL;
	   head->back=NULL;
	   head->x=StartX;
	   head->y=StartY;
	   start_t=clock();
	   GameState=GAME_WAIT;
	   displayScore();
	   putImage(head->x,head->y,COLOR_USER);
	   } break;

      case GAME_WIN:
	   {
	   ptr=head;
	   while (ptr->next!=NULL) {
	     ptr=ptr->next;
	     free(ptr->back);
	   }
	   for (i=500;i<=2000;i+=100)
	    {
	     sound(i);
	     delay(20);
	     nosound();
	     }
	   msgbox(ACT_WIN);
	   if (getch()==0) getch();
	   buildMenu();
	   GameState=GAME_MENU;
	   } break;

      case GAME_OVER:
	   {
	   ptr=head;
	   while (ptr->next!=NULL) {
	     ptr=ptr->next;
	     free(ptr->back);
	   }
	   for (i=1000;i>=400;i-=20)
	    {
	     sound(i);
	     delay(20);
	     nosound();
	     }
	   msgbox(ACT_OVER);
	   if (getch()==0) getch();
	   buildMenu();
	   GameState=GAME_MENU;
	   } break;

      case GAME_WAIT:
	   {
	   getimage(10,250,629,320,gtmp);
	   setcolor(LIGHTCYAN);
	   settextstyle(DEFAULT_FONT,HORIZ_DIR,2);
	   outtextxy(60,270,"Press a key to start or continue..");
	   getch();
	   putimage(10,250,gtmp,COPY_PUT);
	   GameState=GAME_RUN;
	   } break;

      case GAME_EXIT:
	   {
	   } break;

      default: break;
      }
    }
  closegraph();
}

void  getImage(void) {
  unsigned size;

  size=(unsigned int) imagesize(10,250,629,320);
  gtmp=(char far *) malloc(size);

  //-->Back Image Draw And Cut
  setfillstyle(SOLID_FILL,COLOR_BACK);
  bar(0,0,9,9);
  size=(unsigned int) imagesize(0,0,9,9);
  bb=(char far *) malloc(size);
  getimage(0,0,9,9,bb);

  cleardevice();

  //-->User Image Draw And Cut
  setfillstyle(SOLID_FILL,COLOR_USER);
  bar(0,0,9,9);
  size=(unsigned int) imagesize(0,0,9,9);
  bu=(char far *) malloc(size);
  getimage(0,0,9,9,bu);

  cleardevice();

  setcolor(COLOR_FOOD);
  circle(4,4,4);
  setfillstyle(SOLID_FILL,COLOR_FOOD);
  floodfill(4,4,COLOR_FOOD);
  size=(unsigned int) imagesize(0,0,9,9);
  bf=(char far *) malloc(size);
  getimage(0,0,9,9,bf);

  cleardevice();

  setcolor(COLOR_SHIT);
  circle(4,4,4);
  setfillstyle(SOLID_FILL,COLOR_SHIT);
  floodfill(4,4,COLOR_SHIT);
  size=(unsigned int) imagesize(0,0,9,9);
  bs=(char far *) malloc(size);
  getimage(0,0,9,9,bs);

  cleardevice();

  setcolor(YELLOW);
  circle(4,4,4);
  setfillstyle(SOLID_FILL,YELLOW);
  floodfill(4,4,YELLOW);
  size=(unsigned int) imagesize(0,0,9,9);
  bg=(char far *) malloc(size);
  getimage(0,0,9,9,bg);

  cleardevice();

  setfillstyle(SOLID_FILL,BLACK);
  bar(0,0,9,9);
  size=(unsigned int) imagesize(0,0,9,9);
  bc=(char far *) malloc(size);
  getimage(0,0,9,9,bc);

  cleardevice();

  setcolor(LIGHTGRAY);
  circle(2,2,2);
  setfillstyle(SOLID_FILL,LIGHTGRAY);
  floodfill(2,2,LIGHTGRAY);
  setcolor(LIGHTGRAY);
  circle(6,6,2);
  setfillstyle(SOLID_FILL,LIGHTGRAY);
  floodfill(6,6,LIGHTGRAY);
  setcolor(LIGHTGRAY);
  circle(2,6,2);
  setfillstyle(SOLID_FILL,LIGHTGRAY);
  floodfill(3,6,LIGHTGRAY);
  setcolor(LIGHTGRAY);
  circle(6,2,2);
  setfillstyle(SOLID_FILL,LIGHTGRAY);
  floodfill(6,3,LIGHTGRAY);
  size=(unsigned int) imagesize(0,0,9,9);
  br=(char far *) malloc(size);
  getimage(0,0,9,9,br);

  cleardevice();
}

void putImage(short x,short y,short s) {
  switch (s)
    {
    case COLOR_USER:
	 {
	 screen[x][y]=s;
	 putimage(x*10,y*10,bu,COPY_PUT);
	 } break;

    case CLEAR_USER:
	 {
	 screen[x][y]=COLOR_NONE;
	 putimage(x*10,y*10,bc,COPY_PUT);
	 } break;

    case COLOR_SHIT:
	 {
	 screen[x][y]=s;
	 putimage(x*10,y*10,bs,COPY_PUT);
	 } break;

    case COLOR_GOLD:
	 {
	 screen[x][y]=COLOR_GOLD;
	 putimage(x*10,y*10,bg,COPY_PUT);
	 } break;

    case COLOR_FOOD:
	 {
	 screen[x][y]=s;
	 putimage(x*10,y*10,bf,COPY_PUT);
	 } break;

    case COLOR_ROCK:
	 {
	 screen[x][y]=COLOR_ROCK;
	 putimage(x*10,y*10,br,COPY_PUT);
	 } break;

    case COLOR_BACK:
	 {
	 screen[x][y]=s;
	 putimage(x*10,y*10,bb,COPY_PUT);
	 } break;
    }
}

void displayScore(void) {
  char tmpStr[80];
  setfillstyle(SOLID_FILL,BLACK);
  bar(500,0,582,9);
  setcolor(YELLOW);
  settextstyle(DEFAULT_FONT,HORIZ_DIR,1);
  ltoa(Score,tmpStr,10);
  outtextxy(502,3,tmpStr);
}

void msgbox(short act) {
  setfillstyle(SOLID_FILL,LIGHTGRAY);
  bar(147,227,499,259);
  setfillstyle(SOLID_FILL,WHITE);
  bar(144,224,496,256);
  setfillstyle(SOLID_FILL,BLACK);
  bar(146,226,494,254);
  switch (act) {
    case ACT_OVER:
	 setcolor(RED);
	 settextstyle(DEFAULT_FONT,HORIZ_DIR,2);
	 outtextxy(148,234,MSG_GAMEOVER);
	 break;
    case ACT_WIN:
	 setcolor(RED);
	 settextstyle(DEFAULT_FONT,HORIZ_DIR,2);
	 outtextxy(148,234,MSG_GAMEWIN);
	 break;
  }
}

void buildBack(void) {
  int i,s;
  cleardevice();
  for (i=0;i<64;i++)
    {
    for (s=0;s<48;s++)
      {
      screen[i][s]=COLOR_NONE;
      }
    }
  for (i=0;i<48;i++) {
    putImage(0,i,COLOR_BACK);
    putImage(63,i,COLOR_BACK);
  }
  for (i=1;i<63;i++) {
    putImage(i,0,COLOR_BACK);
    putImage(i,47,COLOR_BACK);
  }
  setfillstyle(SOLID_FILL,BLACK);
  bar(0,0,639,479);
  setfillstyle(SOLID_FILL,COLOR_BACK);
  bar(6,6,633,473);
  setfillstyle(SOLID_FILL,BLACK);
  bar(9,9,630,470);
  displayScore();
}

void buildMenu(void) {
  int i;

  cleardevice();

  setcolor(WHITE);
  for (i=0;i<640;i+=10) {
    line(i,10,639,i);
    line(639,479-i,i,479);
  }

  setfillstyle(SOLID_FILL,WHITE);
  bar(50,50,190,140);
  setfillstyle(SOLID_FILL,BLACK);
  bar(52,52,188,138);

  setcolor(LIGHTCYAN);
  settextstyle(DEFAULT_FONT,HORIZ_DIR,1);
  outtextxy(54,54,"  Control Menu  ");
  outtextxy(54,64," -> : Next Level");
  outtextxy(54,74," <- : Back Level");
  outtextxy(54,84," ESC : Exit Game");
  outtextxy(54,94," ENTER : Start !");
  setcolor(RED);
  settextstyle(DEFAULT_FONT,HORIZ_DIR,6);
  outtextxy(120,200,"Hungry");
  outtextxy(70,260,"Snake");
  outtextxy(160,320,"2000");
}

void levelDisplay(void) {
  char tmpStr[80];
  setfillstyle(SOLID_FILL,BLACK);
  bar(93,105,180,130);
  ltoa(level,tmpStr,10);
  setcolor(LIGHTRED);
  settextstyle(DEFAULT_FONT,HORIZ_DIR,3);
  outtextxy(94,106,tmpStr);
}

void readLevelData(short LevelNum) {
  FILE *fp;
  char ch='N';
  int x,y;

  FILE *gfp;
  short counter=0;
  char FileName[]="filename.dat";

  gfp=fopen("levels.dat","r");

  while (LevelNum>0) {
    if (getc(gfp)=='\n') LevelNum--;
  }

  for (counter=0;counter<12;counter++) {
    FileName[counter]=getc(gfp);
  }

  fclose(gfp);

  fp=fopen(FileName,"r");

  MaxFood=0;

  switch (getc(fp)) {
    case '0': speed=1.0;break;
    case '1': speed=0.9;break;
    case '2': speed=0.8;break;
    case '3': speed=0.7;break;
    case '4': speed=0.6;break;
    case '5': speed=0.5;break;
    case '6': speed=0.4;break;
    case '7': speed=0.3;break;
    case '8': speed=0.2;break;
    case '9': speed=0.1;break;
    case 'a': speed=0.09;break;
    case 'b': speed=0.08;break;
    case 'c': speed=0.07;break;
    case 'd': speed=0.06;break;
    case 'e': speed=0.05;break;
    case 'f': speed=0.04;break;
    case 'g': speed=0.03;break;
    case 'h': speed=0.02;break;
    default : speed=0.01;break;
  }

  ch=getc(fp);

  switch (getc(fp)) {
    case 'u': direction=TURN_UP;break;
    case 'd': direction=TURN_DOWN;break;
    case 'r': direction=TURN_RIGHT;break;
    case 'l': direction=TURN_LEFT;break;
  }

  ch=getc(fp);

  for (y=1;y<47;y++) {
    for (x=1;x<63;x++) {
      switch (getc(fp)) {
	case 'f':
	     putImage(x,y,COLOR_FOOD);
	     MaxFood++;
	     break;
	case 'g':
	     putImage(x,y,COLOR_GOLD);
	     break;
	case 's':
	     putImage(x,y,COLOR_SHIT);
	     break;
	case 'r':
	     putImage(x,y,COLOR_ROCK);
	     break;
	case 'u':
	     StartX=x;
	     StartY=y;
	     break;
	default :
	     break;
      }
    }
    ch=getc(fp);
  }

  fclose(fp);
}

void getMaxLevel(void) {
  FILE *fp;
  char ch;

  fp=fopen("levels.dat","r");

  MAXLEVEL=0;
  while ( !feof(fp) ) {
    if (getc(fp)=='\n') MAXLEVEL++;
  }

  fclose(fp);
}