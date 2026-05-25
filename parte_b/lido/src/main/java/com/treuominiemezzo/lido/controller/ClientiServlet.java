package com.treuominiemezzo.lido.controller;

import org.thymeleaf.TemplateEngine;
import org.thymeleaf.context.WebContext;
import org.thymeleaf.web.IWebExchange;
import org.thymeleaf.web.servlet.JakartaServletWebApplication;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import java.io.IOException;

// Questa Servlet risponderà all'URL /clienti
@WebServlet("/clienti")
public class ClientiServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        // Impostiamo il content type per il browser
        resp.setContentType("text/html;charset=UTF-8");

        // 1. Inizializziamo Thymeleaf (usando la nostra classe di configurazione)
        TemplateEngine engine = ThymeleafConfig.getTemplateEngine(req.getServletContext());
        
        // 2. Prepariamo il contesto web (l'equivalente di passare le variabili in PHP)
        JakartaServletWebApplication application = JakartaServletWebApplication.buildApplication(req.getServletContext());
        IWebExchange webExchange = application.buildExchange(req, resp);
        WebContext context = new WebContext(webExchange, webExchange.getLocale());

        // 3. (Qui in futuro faremo la query al database per recuperare i clienti reali)
        // Per ora, passiamo una semplice stringa per verificare che il binding funzioni
        context.setVariable("titoloPagina", "Gestione Clienti (Powered by Java)");
        context.setVariable("messaggio", "Se vedi questo, Thymeleaf ha iniettato i dati con successo!");

        // 4. Diciamo a Thymeleaf di processare il file "clienti.html" e inviarlo al browser
        engine.process("clienti", context, resp.getWriter());
    }
}