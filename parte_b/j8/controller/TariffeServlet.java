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
import java.util.Arrays;
import java.util.List;

@WebServlet("/tariffe")
public class TariffeServlet extends HttpServlet {

  @Override
  protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
    resp.setContentType("text/html;charset=UTF-8");
    
    JakartaServletWebApplication application = JakartaServletWebApplication.buildApplication(getServletContext());
    IWebExchange exchange = application.buildExchange(req, resp);
    WebContext context = new WebContext(exchange, req.getLocale());

    // Passiamo le tipologie alla View esattamente come facevi in PHP
    List<String> tipologie = Arrays.asList("Base", "VIP", "Gazebo", "Disabile");
    context.setVariable("tipologie", tipologie);

    TemplateEngine engine = ThymeleafConfig.getTemplateEngine(getServletContext());
    try {
      engine.process("tariffe", context, resp.getWriter());
    } catch (Exception e) {
      System.err.println("!!! THYMELEAF RENDERING ERROR !!!");
      e.printStackTrace();
    }
  }
}