package com.treuominiemezzo.lido.controller;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.io.PrintWriter;

// L'annotazione @WebServlet mappa automaticamente l'URL a questa classe
// senza dover impazzire con i file XML di configurazione!
@WebServlet("/test")
public class TestServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        // Diciamo al browser che stiamo per mandargli dell'HTML
        resp.setContentType("text/html;charset=UTF-8");
        
        // Scriviamo brutalmente la risposta
        PrintWriter out = resp.getWriter();
        out.println("<!DOCTYPE html>");
        out.println("<html>");
        out.println("<head><title>Test Servlet</title></head>");
        out.println("<body style='font-family: Arial; text-align: center; margin-top: 50px;'>");
        out.println("<h1>🚀 Test Riuscito!</h1>");
        out.println("<p>Se stai leggendo questo messaggio, Java e Tomcat stanno comunicando perfettamente.</p>");
        out.println("<p style='color: gray; font-size: 0.9em;'>Python può esplodere in pace.</p>");
        out.println("</body>");
        out.println("</html>");
    }
}